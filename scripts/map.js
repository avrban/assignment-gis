/*nacitanie mapy a definicie jej zdrojov a vrstiev*/
mapboxgl.accessToken = 'pk.eyJ1IjoiYXZyYmFuIiwiYSI6ImNpdG11eGcydjAwMHQzb3J2cXJtbDBxejgifQ.PTfM9qMio2XDEWLtC7iacw';
var bounds = [
    [19.3909, 48.9923], // Southwest coordinates
    [20.3899, 49.2423]  // Northeast coordinates
];
var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v9',
    center: [19.8431023, 49.0888584],
    zoom: 9
});

map.on('load', function () {
    map.addSource('lokalita', {
        'type': 'geojson',
        'data': {
            'type': 'Feature',
            "geometry": {
                "type": "Point",
                "coordinates": [0, 0]
            }
        }
    });

    map.addLayer({
        'id': 'lokalita',
        'type': 'fill',
        'source': 'lokalita',
        'layout': {},
        'paint': {
            'fill-color': '#088',
            'fill-opacity': 0.2
        }
    });

    map.addSource('trate', {
        type: 'geojson',
        data: {
            "type": "FeatureCollection",
            "features": [{
                "type": "Feature",
                "properties": {},
                "geometry": {
                    "type": "Point",
                    "coordinates": [0, 0]
                }
            }]
        }
    });

    map.addLayer({
        'id': 'trate',
        'type': 'line',
        'source': 'trate',
        'layout': {
            'line-join': 'round',
            'line-cap': 'round'
        },
        'paint': {
            'line-color': '#00838F',
            'line-width': 2
        }
    });

    map.addSource('atrakcie', {
        type: 'geojson',
        data: {
            "type": "FeatureCollection",
            "features": [{
                "type": "Feature",
                "properties": {},
                "geometry": {
                    "type": "Point",
                    "coordinates": [0, 0]
                }
            }]
        }
    });

    map.addLayer({
        "id": "atrakcieMuzea",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "museum-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["==", "tourism", "museum"]
    });

    map.addLayer({
        "id": "atrakciePamiatky",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "monument-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["==", "tourism", "attraction"]
    });

    map.addLayer({
        "id": "atrakciePriroda",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "park-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["any", ["==", "leisure", "recreation_ground"], ["==", "leisure", "nature_reserve"]]
    });

    map.addLayer({
        "id": "atrakcieParky",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "garden-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["any", ["==", "leisure", "park"], ["==", "leisure", "picnic_table"]]
    });

    map.addLayer({
        "id": "atrakcieAquaparky",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "swimming-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["==", "leisure", "water_park"]
    });

    map.addLayer({
        "id": "atrakciePlavby",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "harbor-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["==", "leisure", "marina"]
    });

    map.addLayer({
        "id": "atrakcieSportoviska",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "stadium-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["any", ["==", "leisure", "sports_centre"], ["==", "leisure", "track"], ["==", "leisure", "stadium"], ["==", "leisure", "miniature_golf"], ["==", "leisure", "horse_riding"]]
    });

    map.addLayer({
        "id": "atrakcieLyziarske",
        "type": "symbol",
        "source": "atrakcie",
        "layout": {
            "visibility": "visible",
            "icon-image": "skiing-15",
            "text-field": "{name}",
            "text-font": ["Open Sans Semibold", "Arial Unicode MS Regular"],
            "text-offset": [0, 1.2],
            "text-anchor": "top"
        },
        "filter": ["any", ["==", "leisure", "ski_resort"]]
    });
});
