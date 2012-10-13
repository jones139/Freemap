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

    def getSRTMData(self,sysCfg):
        """ Downloads SRTM data, converts it to contours, and uploads
        it into a postgresql database.
        26sep2011 GJ  ORIGINAL VERSION
        """
        print "getSRTMData"
        origWd = os.getcwd()
        srtmTmpDirName = "srtm_tmp"
        mergeHgt = "srtm.hgt"
        mergeTif = "srtm.tiff"
        contoursShp = "contours.shp"
        hillshadeTif = "hillshade.tiff"

        jobDir = sysCfg['jobDir']
        srtmTmpDir = "%s" % (jobDir)

        downloader = srtm.SRTMDownloader(cachedir=sysCfg['srtmDir'])
        downloader.loadFileList()
        ll = sysCfg['ll']
        print "ll=",ll
        tileSet = downloader.getTileSet(ll)
        print tileSet

        if not os.path.exists(srtmTmpDir):
            os.makedirs(srtmTmpDir)
        os.chdir(srtmTmpDir)
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
                    os.system("unzip %s" % (tileFnameZip))

                print "Generating Contour Lines...."
                os.system("gdal_contour -i 10 -snodata 32767 -a height %s %s" %
                          (tileFname,contourFname))
            os.chdir(srtmTmpDir)
            # create symbolic link to contours shape file.
            print "Linking contours file"
            contourFnameBase = contourFname.split(".shp")[0]
            fnameBase = contourFnameBase.split("/")[-1]
            for ext in self.shpExt:
                fname = "%s.%s" % (fnameBase,ext)
                if os.path.exists(fname):
                    os.remove(fname)
                os.symlink("%s.%s" % (contourFnameBase,ext),
                           "%s" % (fname))
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
                print "Generating Hillshading overlay image...."
                print "      re-projecting SRTM data to map projection..."
                os.system("gdalwarp -of GTiff -co \"TILED=YES\" -srcnodata 32767 -t_srs \"+proj=merc +ellps=sphere +R=6378137 +a=6378137 +units=m\" -rcs -order 3 -tr 30 30 -multi %s %s" % (tileFname,mergeTif))
                print "      generating hillshade image...."
                os.system("gdaldem hillshade  %s %s -z 2" % (mergeTif,hillshadeFname))
                # Remove the temporary reprojected geotiff.
                os.remove(mergeTif)

            os.chdir(srtmTmpDir)
            # create symbolic link hillshade tiff file.
            print "Linking hillshade file"
            fname = hillshadeFname.split("/")[-1]
            if os.path.exists(fname):
                os.remove(fname)
            os.symlink("%s" % (hillshadeFname),
                       "%s" % (fname))

            # Remove the uncompressed raw srtm tile from the cache.
            if os.path.exists(tileFname):
                print "removing uncompressed srtm file from cache..."
                os.remove("%s" % (tileFname))

        os.chdir(origWd)




if __name__ == "__main__":
    print "dataMgr.py"
    sysCfg = {}
    sysCfg['ll'] = (-6.5,49.5,2.1,59.0)
    sysCfg['srtmDir'] = "/home/disk2/graham/Freemap/srtm/srtm/raw"
    sysCfg['jobDir']  = "/home/disk2/graham/Freemap/srtm/srtm/processed"
    dm = dataMgr(sysCfg)
    dm.getSRTMData(sysCfg)
    print "done"

