#!/usr/bin/python
#
#    This file is part of printmaps - a simple utility to produce a
#    printable (pdf) maps from OpenStreetMap data.
#
#    Printmaps is free software: you can redistribute it and/or modify
#    it under ther terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    Printmaps is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with townguide.  If not, see <http://www.gnu.org/licenses/>.
#
#    Copyright Graham Jones 2009, 2010, 2012
#
"""
dataMgr - manages map data ready for rendering using mapnik.
"""

import srtm
import os
import getProjStr

class dataMgr:
    def __init__(self,sysCfg):
        print "dataMgr.__init__()"
        self.sysCfg = sysCfg
        print sysCfg

        self.shpExt = ["shp","prj","shx","dbf"]

    def reprojectTiff(self,inFname,outFname):
        """ Reproject the geotiff named inFname to the google project
        ESPG:900913, and save it to outFname.
        14oct2012  GJ ORIGINAL VERSION
        """
        if not os.path.exists(inFname):
            print "reprojectTiff - input file %s does not exist - doing nothing." % inFname
            return
        if os.path.exists(outFname):
            print "reprojectTiff - removing output file %s." % outFname
            # Remove the temporary reprojected geotiff.
            os.remove(outFname)

        print "      re-projecting SRTM data to map projection..."
        os.system("gdalwarp -of GTiff -co \"TILED=YES\" -srcnodata 32767 -t_srs \"+proj=merc +ellps=sphere +R=6378137 +a=6378137 +units=m\" -rcs -order 3 -tr 30 30 -multi %s %s" % (inFname,outFname))
        print "reprojectTiff finished..."


    def getSRTMData(self,sysCfg):
        """ Downloads SRTM data, converts it to contours, and uploads
        it into a postgresql database.
        26sep2011 GJ  ORIGINAL VERSION
        """
        print "getSRTMData"
        origWd = os.getcwd()
        reprojTif = "srtm_reproj.tiff"

        srtmTmpDir = "%s" % (sysCfg['srtmTmpDir'])

        downloader = srtm.SRTMDownloader(cachedir=sysCfg['srtmDir'])
        downloader.loadFileList()
        ll = sysCfg['ll']
        print "ll=",ll
        tileSet = downloader.getTileSet(ll)
        print tileSet

        if not os.path.exists(srtmTmpDir):
            os.makedirs(srtmTmpDir)
        #os.chdir(srtmTmpDir)
        isFirst = True
        for tileFnameZip in tileSet:
            tileFname = tileFnameZip.split(".zip")[0]
            fnameZipParts = tileFnameZip.split("/")
            # The compressed file, without the path
            fname = fnameZipParts[-1]
            print tileFnameZip,fname
            os.chdir(sysCfg['srtmDir'])

            ######################################################
            # Get the pre-generated contours shapefile for this 
            # srtm tile if it exists.
            contourFname = "%s%s" % (tileFname,".contours.shp")
            print "contourFname=%s" % contourFname
            if not os.path.exists(contourFname):
                print "contour File does not exist - creating..."
                os.chdir(sysCfg['srtmDir'])
                print tileFnameZip,fname
                # uncompress the raw srtm file.
                if not os.path.exists(tileFname):
                    print "uncompressing %s." % tileFnameZip
                    os.system("unzip %s" % (tileFnameZip))
                print "Reprojecting...."
                self.reprojectTiff(tileFname,reprojTif)
                if os.path.exists(reprojTif):
                    print "Generating Contour Lines...."
                    os.system("gdal_contour -i 10 -snodata 32767 -a height %s %s" %
                              (reprojTif,contourFname))
                else:
                    print "Oh no - reprojected geoTiff %s does not exist...."\
                        % reprojTif
                    

            ########################################################
            # Create SQL required to upload contours to postgresql database
            # This needs to be done separately with psql -f ***.sql.
            contourSqlFname = "%s%s" % (tileFname,".contours.sql");

            # Create SQL to initialise the database if necessary
            contourSqlInitFname = "contours_init.sql"
            if not os.path.exists(contourSqlInitFname):
                os.system("shp2pgsql -p -s 900913 %s contours > %s" %
                          (contourFname,contourSqlInitFname))

            if not os.path.exists(contourSqlFname):
                if os.path.exists(contourFname):
                    shp2pgsqlOpts = "-a"
                    os.system("shp2pgsql %s -s 900913 %s contours > %s" % 
                      (shp2pgsqlOpts,contourFname,contourSqlFname))
            else:
                print "Error - Contour shape file %s does not exist.." % contourFname
            ###############################################################
            # Get the pre-generated hillshade .tiff file if it exists.
            hillshadeFname = "%s%s" % (tileFname,".hillshade.tiff")
            print "hillshadeFname=%s" % hillshadeFname
            if not os.path.exists(hillshadeFname):
                print "hillshade File does not exist - creating..."
                # uncompress the raw srtm file.
                if not os.path.exists(tileFname):
                    print "uncompressing DEM file %s." % tileFnameZip
                    os.chdir(sysCfg['srtmDir'])
                    os.system("unzip %s" % (tileFnameZip))
                    if not os.path.exists(tileFname):
                        print "****ERRROR - SOMETHING HAS GONE WRONG ****"
                        print "%s still does not exist...." % (tileFname)
                        print "it is probably in %s" % (srtmTmpDir)
                print "Generating Hillshade file...."
                if not os.path.exists(reprojtif):
                    self.reprojectTiff(tileFname,reprojTif)
                print "Generating Hillshading overlay image...."
                print "      generating hillshade image...."
                os.system("gdaldem hillshade  %s %s -z 2" % (reprojTif,hillshadeFname))
                # Remove the temporary reprojected geotiff.
                if os.path.exists(reprojTif):
                    os.remove(reprojTif)

            # Remove the uncompressed raw srtm tile from the cache.
            if os.path.exists(tileFname):
                print "removing uncompressed srtm file from cache..."
                os.remove("%s" % (tileFname))

        os.chdir(origWd)




if __name__ == "__main__":
    print "dataMgr.py"
    sysCfg = {}
    sysCfg['ll'] = (-6.5,49.5,2.1,59.0)
    sysCfg['srtmDir'] = "/home/disk2/graham/Freemap/srtm/data"
    sysCfg['srtmTmpDir']  = "/home/disk2/graham/Freemap/srtm/tmp"
    dm = dataMgr(sysCfg)
    dm.getSRTMData(sysCfg)
    print "done"

