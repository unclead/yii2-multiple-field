#Yii2 Multiple input widget.

[![Join the chat at https://gitter.im/unclead/yii2-multiple-input](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/unclead/yii2-multiple-input?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
Yii2 widget for handle multiple inputs for an attribute of model

[![Latest Stable Version](https://poser.pugx.org/unclead/yii2-multiple-input/v/stable)](https://packagist.org/packages/unclead/yii2-multiple-input) [![Total Downloads](https://poser.pugx.org/unclead/yii2-multiple-input/downloads)](https://packagist.org/packages/unclead/yii2-multiple-input) [![Latest Unstable Version](https://poser.pugx.org/unclead/yii2-multiple-input/v/unstable)](https://packagist.org/packages/unclead/yii2-multiple-input) [![License](https://poser.pugx.org/unclead/yii2-multiple-input/license)](https://packagist.org/packages/unclead/yii2-multiple-input)

##Installation


The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require  unclead/yii2-multiple-input "~1.0"
```

or add

```
"unclead/yii2-multiple-input": "~1.0"
```

to the require section of your `composer.json` file.

##Usage

### Input with one column

![Single column example](./docs/images/single-column.gif?raw=true)

For example you want to have an ability of entering several emails of user on profile page.
In this case you can use yii2-multiple-input widget like in the following code

```php
use unclead\widgets\MultipleInput;

<?= $form->field($model, 'emails')->widget(MultipleInput::className(), [
        'limit' => 5
    ])
    ->label(false);
?>
```

You can find more detail about this use case [here](docs/single_column.md)

### Input with multiple column in each row

![Multiple columns example](./docs/images/multiple-column.gif?raw=true)

For example you keep some data in json format in attribute of model. Imagine that it is an abstract user schedule with keys: user_id, day, priority

On the edit page you want to be able to manage this schedule and you can you yii2-multiple-input widget like in the following code

```php

use unclead\widgets\MultipleInput;
<?= $form->field($model, 'schedule')->widget(MultipleInput::className(), [
    'limit' => 4,
    'columns' => [
        [
            'name'  => 'user_id',
            'type'  => 'dropDownList',
            'title' => 'User',
            'defaultValue' => 1,
            'items' => [
                1 => 'User 1',
                2 => 'User 2'
            ]
        ],
        [
            'name'  => 'day',
            'type'  => \kartik\date\DatePicker::className(),
            'title' => 'Day',
            'value' => function($data) {
                return $data['day'];
            },
            'items' => [
                '0' => 'Saturday',
                '1' => 'Monday'
            ],
            'options' => [
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy',
                    'todayHighlight' => true
                ]
            ],
            'headerOptions' => [
                'style' => 'width: 250px;',
                'class' => 'day-css-class'
            ]
        ],
        [
            'name'  => 'priority',
            'title' => 'Priority',
            'options' => [
                'class' => 'input-priority'
            ]
        ],
        [
            'name'  => 'comment',
            'type'  => 'static',
            'value' => function($data) {
                return Html::tag('span', 'static content', ['class' => 'label label-info']);
            },
            'headerOptions' => [
                'style' => 'width: 70px;',
            ]
        ]
    ]
 ]);
?>
```

You can find more detail about this use case [here](docs/multiple_columns.md)

> Also you can find source code of examples [here](./docs/examples/)

## Configuration

Widget support the following options that are additionally recognized over and above the configuration options in the InputWidget:

- `limit`: *integer*: rows limit. If not set will defaul to unlimited
- `columns` *array*: the row columns configuration where you can set the following properties:
  - `name` *string*: input name. *Required options*
  - `type` *string*: type of the input. If not set will default to `textInput`. Read more about the types described below
  - `title` *string*: the column title
  - `value` *Closure*: you can set it to an anonymous function with the following signature: ```function($data) {}```
  - `defaultValue` *string*: default value of input,
  - `items` *array*: the items for input with type dropDownList, listBox, checkboxList, radioList
  - `options` *array*: the HTML attributes for the input
  - `headerOptions` *array*: the HTML attributes for the header cell

### Input types

Each column in a row can has their own type. Widget supports:

- all yii2 html input types:
  - `textInput`
  - `dropDownList`
  - `radioList`
  - `textarea`
  - For more detail look at [Html helper class](http://www.yiiframework.com/doc-2.0/yii-helpers-html.html)
- input widget (widget that extends from `InputWidget` class). For example, `yii\widgets\MaskedInput`

For using widget as column input you may use the following code:

```php
[
    'name'  => 'phone',
    'title' => 'Phone number',
    'type' => \yii\widgets\MaskedInput::className(),
    'options' => [
        'class' => 'input-phone',
        'mask' => '999-999-99-99'
    ]
]
```

### JavaScript events
This widget has following events:
 - `init`: triggered after initialization
 - `addNewRow`: triggered after new row insertion
 - `removeRow`: triggered after row removal

Example:
```js
jQuery('#multiple-input').on('addNewRow', function() {
   //some code 
});
```

##License

**yii2-multiple-input** is released under the BSD 3-Clause License. See the bundled [LICENSE.md](./LICENSE.md) for details.
