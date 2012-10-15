
function Freemap(lat,lon,zoom)
{
//    var tileUrl = 'http://www.free-map.org.uk/0.6/ws/tsvr.php' +
//        '?x={x}&y={y}&z={z}&way=all&poi=all&kothic=1&contour=1&coastline=1';
//    var tileUrl = 'http://maps.webhop.net/Freemap/0.6/ws/tsvr.php' +
//        '?x={x}&y={y}&z={z}&way=all&poi=all&kothic=1&contour=0&coastline=1';
    var tileUrl = 'http://freemap.maps3.org.uk/webCache/tcache.php' +
        '?x={x}&y={y}&z={z}';

    this.kothic=new L.TileLayer.Kothic(tileUrl,{minZoom:4,
            attribution: 'Map data &copy; 2012 OpenStreetMap contributors,'+
                'contours &copy; Crown Copyright and database right '+
                'Ordnance Survey 2011, Rendering by '+
                '<a href="http://github.com/kothic/kothic-js">Kothic JS</a>'} );

    var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var osmAttrib = 'Map data &copy; 2012 OpenStreetMap contributors'
    this.osmLayer = new L.TileLayer(osmUrl, 
				    {minZoom: 4, 
				     attribution: osmAttrib,
				     opacity:0.25
				    });
    

    this.map = new L.Map('map',{layers:[this.osmLayer,this.kothic]});
    var layerControl = new L.Control.Layers({'osm':this.osmLayer},
					    {'freemap':this.kothic});
    this.map.addControl(layerControl);

    if(lat===null) 
    {
        lat = (window.localStorage && 
            window.localStorage.getItem("lat")!==null) 
            ? window.localStorage.getItem("lat") : 51.05;
    }
    if(lon===null) 
    {
        lon = (window.localStorage && 
            window.localStorage.getItem("lon")!==null) 
            ? window.localStorage.getItem("lon") : -0.72; 
    }
    if(zoom===null) 
    {
        zoom = (window.localStorage && 
            window.localStorage.getItem("zoom")!==null) 
            ? window.localStorage.getItem("zoom") : 14;
    }

    var startPos= new L.LatLng(lat,lon);
    this.map.setView(new L.LatLng(lat,lon),zoom).addLayer(this.kothic);
    //this.map.addEventListener('moveend',this.updatePermaLink(this));
    //this.map.addEventListener('zoomend',this.updatePermaLink(this));
    this.map.on('dragend',this.updatePermaLink.bind(this));
    this.map.on('zoomend',this.updatePermaLink.bind(this));
    this.updatePermaLink();
}


Freemap.prototype.setLocation = function(x,y)
{
    this.map.panTo(new L.LatLng(y,x));
    this.saveLocation();
}

Freemap.prototype.updatePermaLink = function() {
    // update the permalink on the main map page based on the current map
    // state.
    var centrePt = this.map.getCenter();
    var curLat = centrePt.lat;
    var curLon = centrePt.lng;
    var curZoom = this.map.getZoom();
    var pageURL = document.location.href.split('?')[0];
    var hrefURL = [pageURL, '?lon=', curLon, '&lat=', curLat, '&zoom=',
        curZoom].join('');
    document.getElementById("permaLink").href=hrefURL;
}



function init()
{
    var freemap = new Freemap(lat,lon,zoom);
}
