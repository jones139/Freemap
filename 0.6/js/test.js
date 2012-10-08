
function Freemap(lat,lon,zoom)
{
//    var tileUrl = 'http://www.free-map.org.uk/0.6/ws/tsvr.php' +
//        '?x={x}&y={y}&z={z}&way=all&poi=all&kothic=1&contour=1&coastline=1';
//    var tileUrl = 'http://maps.webhop.net/Freemap/0.6/ws/tsvr.php' +
//        '?x={x}&y={y}&z={z}&way=all&poi=all&kothic=1&contour=0&coastline=1';
    var tileUrl = 'http://freemap.maps3.org.uk/webCache/tcache.php' +
        '?x={x}&y={y}&z={z}';

    this.kothic=new L.TileLayer.Kothic(tileUrl,{minZoom:11,
            attribution: 'Map data &copy; 2012 OpenStreetMap contributors,'+
                'CC-by-SA,'+
                'contours &copy; Crown Copyright and database right '+
                'Ordnance Survey 2011, Rendering by '+
                '<a href="http://github.com/kothic/kothic-js">Kothic JS</a>'} );

    this.map = new L.Map('map',{layers:[this.kothic]});
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
}


Freemap.prototype.setLocation = function(x,y)
{
    this.map.panTo(new L.LatLng(y,x));
    this.saveLocation();
}


function init()
{
    var freemap = new Freemap(lat,lon,zoom);
}
