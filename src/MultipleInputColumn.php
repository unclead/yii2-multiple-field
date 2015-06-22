<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets;

use Closure;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class MultipleInputColumn
 * @package unclead\widgets
 */
class MultipleInputColumn extends Object
{
    const TYPE_TEXT_INPUT       = 'textInput';
    const TYPE_HIDDEN_INPUT     = 'hiddenInput';
    const TYPE_DROPDOWN         = 'dropDownList';
    const TYPE_LISTBOX          = 'listBox';
    const TYPE_CHECKBOX_LIST    = 'checkboxList';
    const TYPE_RADIO_LIST       = 'radioList';
    const TYPE_STATIC           = 'static';

    /**
     * @var string input name
     */
    public $name;

    /**
     * @var string the header cell content. Note that it will not be HTML-encoded.
     */
    public $title;

    /**
     * @var string input type
     */
    public $type;

    /**
     * @var string|Closure
     */
    public $value;

    /**
     * @var mixed default value for input
     */
    public $defaultValue = '';

    /**
     * @var array
     */
    public $items;

    /**
     * @var array
     */
    public $options;

    /**
     * @var array the HTML attributes for the header cell tag.
     */
    public $headerOptions = [];

    /**
     * @var MultipleInput
     */
    public $widget;

    public function init()
    {
        parent::init();

        if (empty($this->name)) {
            throw new InvalidConfigException("The 'name' option is required.");
        }

        if (is_null($this->type)) {
            $this->type = self::TYPE_TEXT_INPUT;
        }

        if (empty($this->options)) {
            $this->options = [];
        }
    }

    /**
     * @return bool whether the type of column is hidden input.
     */
    public function isHiddenInput()
    {
        return $this->type == self::TYPE_HIDDEN_INPUT;
    }


    /**
     * @return bool whether the column has a header
     */
    public function hasHeader()
    {
        return !empty($this->title);
    }

    /**
     * Renders the header cell.
     * @return null|string
     */
    public function renderHeaderCell()
    {
        $options = $this->headerOptions;
        Html::addCssClass($options, 'list-cell__' . $this->name);
        if ($this->isHiddenInput()) {
            Html::addCssClass($options, 'list-cell__hidden');
        }
        return Html::tag('th', $this->title, $options);
    }

    /**
     * Prepares the value of column.
     *
     * @param array|ActiveRecord $data
     * @return mixed
     */
    public function prepareValue($data)
    {
        if ($this->value !== null) {
            $value = $this->value;
            if ($value instanceof \Closure) {
                $value = call_user_func($value, $data);
            }
        } else {
            if ($data instanceof ActiveRecord) {
                $value = $data->getAttribute($this->name);
            } elseif (is_array($data)) {
                $value = ArrayHelper::getValue($data, $this->name, null);
            } elseif(is_string($data)) {
                $value = $data;
            }else {
                $value = $this->defaultValue;
            }
        }
        return $value;
    }

    /**
     * Renders the cell content.
     *
     * @param string $value placeholder of the input's value
     * @return string
     * @throws InvalidConfigException
     */
    public function renderCellContent($value)
    {
        $type = $this->type;
        $name = $this->widget->getElementName($this->name);

        $options = $this->options;
        $options['id'] = $this->widget->getElementId($this->name);
        Html::addCssClass($options, 'form-control');

        switch ($this->type) {
            case self::TYPE_HIDDEN_INPUT:
                $input = Html::hiddenInput($name, $value, $options);
                break;
            case self::TYPE_DROPDOWN:
            case self::TYPE_LISTBOX:
            case self::TYPE_CHECKBOX_LIST:
            case self::TYPE_RADIO_LIST:
                $options['selectedOption'] = $value;
                $input = Html::$type($name, null, $this->items, $options);
                break;
            case self::TYPE_STATIC:
                $input = Html::tag('p', $value, ['class' => 'form-control-static']);
                break;
            default:
                if (method_exists('yii\helpers\Html', $type)) {
                    $input = Html::$type($name, $value, $options);
                } elseif (class_exists($type) && method_exists($type, 'widget')) {
                    $input = $type::widget(array_merge($options, [
                        'name'  => $name,
                        'value' => $value,
                    ]));
                } else {
                    throw new InvalidConfigException("Invalid column type '$type'");
                }
        }

        $input = Html::tag('div', $input, [
            'class' => 'form-group field-' . $options['id'],
        ]);
        $cellOptions = ['class' => 'list-cell__' . $this->name];
        if ($this->isHiddenInput()) {
            Html::addCssClass($cellOptions, 'list-cell__hidden');
        }
        return Html::tag('td', $input, $cellOptions);
    }
}