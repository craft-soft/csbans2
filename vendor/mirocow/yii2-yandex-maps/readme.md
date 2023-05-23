# Yii2 Yandex Maps Components #

[![Latest Stable Version](https://poser.pugx.org/mirocow/yii2-yandex-maps/v/stable)](https://packagist.org/packages/mirocow/yii2-yandex-maps) [![Latest Unstable Version](https://poser.pugx.org/mirocow/yii2-yandex-maps/v/unstable)](https://packagist.org/packages/mirocow/yii2-yandex-maps) [![Total Downloads](https://poser.pugx.org/mirocow/yii2-yandex-maps/downloads)](https://packagist.org/packages/mirocow/yii2-yandex-maps) [![Daily Downloads](https://poser.pugx.org/mirocow/yii2-yandex-maps/d/daily)](https://packagist.org/packages/mirocow/yii2-yandex-maps)  [![License](https://poser.pugx.org/mirocow/yii2-yandex-maps/license)](https://packagist.org/packages/mirocow/yii2-yandex-maps) 

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

### Add repositor


```json
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/mirocow/yii2-yandex-maps.git"
        }
    ]
```

and then

```
php composer.phar require --prefer-dist "mirocow/yii2-yandex-maps" "*"
```

or add

```json
"mirocow/yii2-yandex-maps" : "*"
```

to the require section of your application's `composer.json` file.

* * *

For last Yii2 2.X version please use patch https://github.com/iamruslan/yii2-yandex-maps/commit/fee95f91b4b313424c5041101f57a6b49d0a7276

## Components ##

- [`mirocow\yandexmaps\Api`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapsapi)
- [`mirocow\yandexmaps\Map`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapsmap)
- [`mirocow\yandexmaps\Canvas`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapscanvas)
- [`mirocow\yandexmaps\Placemark`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapsplacemark)
- [`mirocow\yandexmaps\Polygon`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapspolygon)
- [`mirocow\yandexmaps\Controls`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapscontrols)
- [`mirocow\yandexmaps\Polyline`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapspolyline)
- [`mirocow\yandexmaps\GeoObject`](https://github.com/mirocow/yii2-yandex-maps#mirocowyandexmapsgeoobject)
- TODO: [Geo XML](http://api.yandex.ru/maps/doc/jsapi/2.x/dg/concepts/geoxml.xml)
- TODO: [Balloon](http://api.yandex.ru/maps/doc/jsapi/2.x-stable/ref/reference/Balloon.xml)
- TODO: [Hint](http://api.yandex.ru/maps/doc/jsapi/2.x-stable/ref/reference/Hint.xml)
- TODO: [Clusterer](http://api.yandex.ru/maps/doc/jsapi/2.x/ref/reference/Clusterer.xml)

### mirocow\yandexmaps\Api ###

Application components which register scripts.

__Usage__

Attach component to application (e.g. edit config/main.php):
```php
'components' => [
	'yandexMapsApi' => [
		'class' => 'mirocow\yandexmaps\Api',
	]
 ],
```

### mirocow\yandexmaps\Map ###

Map instance.

__Usage__

```php
    $map = new \mirocow\yandexmaps\Map('yandex_map', [
            'center' => [55.7372, 37.6066],
            'zoom' => 10,
            // Enable zoom with mouse scroll
            'behaviors' => array('default', 'scrollZoom'),
            'type' => "yandex#map",
        ], 
        [
            // Permit zoom only fro 9 to 11
            'minZoom' => 9,
            'maxZoom' => 11,
            'controls' => [
              "new ymaps.control.SmallZoomControl()",
              "new ymaps.control.TypeSelector(['yandex#map', 'yandex#satellite'])",  
            ],                    
        ]                
    );             
```

### mirocow\yandexmaps\Canvas ###

This is widget which render html tag for your map.

__Usage__

Simple add widget to view:
```php

echo \mirocow\yandexmaps\Canvas::widget([
        'htmlOptions' => [
            'style' => 'height: 400px;',
        ],
        'map' => $map,
    ]);
```

### mirocow\yandexmaps\Controls ###

```php
      'controls' => [
          // v 2.1
          'new ymaps.control.ZoomControl({options: {size: "small"}})',
          //'new ymaps.control.TrafficControl({options: {size: "small"}})',
          //'new ymaps.control.GeolocationControl({options: {size: "small"}})',
          'search' => 'new ymaps.control.SearchControl({options: {size: "small"}})',
          //'new ymaps.control.FullscreenControl({options: {size: "small"}})',
          //'new ymaps.control.RouteEditor({options: {size: "small"}})',
      ],
```

### mirocow\yandexmaps\GeoObject ###

#### mirocow\yandexmaps\Placemark ####

```php
    $placemark = new mirocow\yandexmaps\objects\Placemark([
            55.7372,
            37.6066
    ], [

    ], [
            'draggable' => true
      ]
    );
```

#### mirocow\yandexmaps\Polygon ####

TODO:

#### mirocow\yandexmaps\Clusterer ####

```js
    for (var i in map_point) {
    points[i] = new ymaps.GeoObject({
     geometry : {
      type: 'Point',
      coordinates : [map_point[i]['lat'],map_point[i]['lng']]
     },
     properties : {
      balloonContentBody : map_point[i]['body']
      // hintContent : 'подробнее'
     }
    },
    {
     iconImageHref: '/i/' + map_point[i]['spec']+'.png',
     iconImageSize: [29,29],
     balloonIconImageHref: '/i/' + map_point[i]['spec']+'.png',
     balloonIconImageSize: [29,29],
     hasBalloon: true
    });
   }

   var clusterer = new ymaps.Clusterer();
   clusterer.add(points);
   map.geoObjects.add(clusterer);
```

#### mirocow\yandexmaps\Polyline ####

TODO:

## Examples: ##

## User form with yandex map: ##

```php
<?php
$form = ActiveForm::begin([
            'options' => ['class' => 'user-settings'],
            'fieldConfig' => [
                'options' => [
                    'tag' => false,
                ],
            ],
        ]);

        $map = new \mirocow\yandexmaps\Map('yandex_map', [
          'center' => [55.7372, 37.6066],
          'zoom' => 10,
          // Enable zoom with mouse scroll
          'behaviors' => ['default', 'scrollZoom'],
          'type' => "yandex#map",
          'controls' => [],
        ],
          [
              // Permit zoom only fro 9 to 11
              'minZoom' => 1,
              'maxZoom' => 11,
              'controls' => [
                  // v 2.1
                  'new ymaps.control.ZoomControl({options: {size: "small"}})',
                  //'new ymaps.control.TrafficControl({options: {size: "small"}})',
                  //'new ymaps.control.GeolocationControl({options: {size: "small"}})',
                  'search' => 'new ymaps.control.SearchControl({options: {size: "small"}})',
                  //'new ymaps.control.FullscreenControl({options: {size: "small"}})',
                  //'new ymaps.control.RouteEditor({options: {size: "small"}})',
              ],
              'behaviors' => [
                'scrollZoom' => 'disable',
              ],
              'objects' => [
                <<<JS
search.events.add("resultselect", function (result){

    // Remove old coordinates
    \$Maps['yandex_map'].geoObjects.each(function(obj){
        \$Maps['yandex_map'].geoObjects.remove(obj);
    });  

    // Add selected coordinates
    var index = result.get('index');
    var searchControl = \$Maps['yandex_map'].controls.get(1);
    searchControl.getResult(index).then(function(res) {
        var coordinates = res.geometry.getCoordinates();
        $('#coordinates').html('');
        $('#coordinates').append('<input type="hidden" name="User[coordinates][]" value="'+coordinates[0]+'">');
        $('#coordinates').append('<input type="hidden" name="User[coordinates][]" value="'+coordinates[1]+'">');
    });
    
});
JS

                                      ],
                                  ]
                );?>

                <?= \mirocow\yandexmaps\Canvas::widget([
                  'htmlOptions' => [
                    'style' => 'height: 400px;',
                  ],
                  'map' => $map,
                ]);

                ?>

                <div id="coordinates"></div>
                
<?php ActiveForm::end(); ?>
```