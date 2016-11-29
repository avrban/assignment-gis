$(document).ready(function () {
    document.getElementById('datum').valueAsDate = new Date();

    /*autocomplete*/
    $('input#lokalita').typeahead({
        name: 'lokalita',
        remote: 'api/api.php?q=%QUERY',
        minLength: 3
    });

    /*odoslanie formulara*/
    $('#submitbtn').click(function () {
        $(this).button('loading');
        var atrakcieNacitane = false;
        var trateNacitane = false;

        var typy = new Array();
        $("input[name='typy[]']:checked").each(function () {
            typy.push($(this).val());
        });

        var trate = new Array();
        $("input[name='trate[]']:checked").each(function () {
            trate.push($(this).val());
        });

        /*nacitanie zvolenej lokality*/
        $.post("api/api.php",
            {
                lokalita: document.getElementById('lokalita').value,
                get_locality: true
            },
            function (data, status) {
                 $.each(JSON.parse(data).features, function () {
                    map.flyTo({center: [this.properties.centroidX, this.properties.centroidY], zoom: 10});
                });
                map.getSource('lokalita').setData(JSON.parse(data));
            }
        );

        /*VYSLEDKY VYHLADAVANIA*/
        $('#list').html("<h1>Výsledky vyhľadávania</h1>");

        /*nacitanie atrakcii*/
        if (typy.length > 0) {
            $.post("api/api.php",
                {
                    lokalita: document.getElementById('lokalita').value,
                    vzdialenost: document.getElementById('vzdialenost').value,
                    datum: document.getElementById('datum').value,
                    hodiny: $('input#hodiny').prop('checked'),
                    typy: typy,
                    get_attractions: true
                },
                function (data, status) {
                    var zoznam = '<h2>Atrakcie</h2><ul>';

                    $.each(JSON.parse(data).features, function () {
                        zoznam += "<li><a href='javascript:map.flyTo({center: [" + this.properties.centroidX + "," + this.properties.centroidY + "], zoom:13})'><h3>" + this.properties.name + "</h3><div><span class='distance'>" + (this.properties.distance / 1000).toFixed(2) + " km</span><span class='pull-right hours'>" + this.properties.hours + "</span></div></a></li>";
                    });

                    zoznam += '</ul>';
                    $('#list').append(zoznam);

                    map.getSource('atrakcie').setData(JSON.parse(data));
                    atrakcieNacitane = true;

                    if (((typy.length > 0 && atrakcieNacitane == true) || typy.length == 0) && ((trate.length > 0 && trateNacitane == true) || trate.length == 0)) {
                        $('#submitbtn').button('reset');
                        $('#list').show();
                        $('#sidebar').animate({
                            scrollTop: $("#list").offset().top
                        }, 500);
                    }

                }
            );
        }

        /*nacitanie trati*/
        if (trate.length > 0) {
            $.post("api/api.php",
                {
                    lokalita: document.getElementById('lokalita').value,
                    vzdialenost: document.getElementById('vzdialenost').value,
                    trate: trate,
                    get_tracks: true
                },
                function (data, status) {
                    var zoznam = "<h2>Trate a chodníky</h2><ul>";
                    $.each(JSON.parse(data).features, function () {
                        zoznam += "<li><a href='javascript:map.flyTo({center: [" + this.properties.centroidX + "," + this.properties.centroidY + "], zoom:13})'><h3>" + this.properties.name + "</h3><div><span class='distance'>" + (this.properties.distance / 1000).toFixed(2) + " km</span><span class='pull-right length'>dĺžka: " + (this.properties.length / 1000 * 1000).toFixed(0) + " m</span></div></a></li>";
                    });

                    zoznam += "</ul>"
                    $('#list').append(zoznam);

                    map.getSource('trate').setData(JSON.parse(data));
                    trateNacitane = true;

                    if (((typy.length > 0 && atrakcieNacitane == true) || typy.length == 0) && ((trate.length > 0 && trateNacitane == true) || trate.length == 0)) {
                        $('#submitbtn').button('reset');
                        $('#list').show();
                        $('#sidebar').animate({
                            scrollTop: $("#list").offset().top
                        }, 500);
                    }
                }
            );
        }

    })
});

