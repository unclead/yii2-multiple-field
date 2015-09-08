<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets\components;

use Closure;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * Class BaseColumn.
 *
 * @package unclead\widgets\components
 */
abstract class BaseColumn extends Object
{
    const TYPE_TEXT_INPUT       = 'textInput';
    const TYPE_HIDDEN_INPUT     = 'hiddenInput';
    const TYPE_DROPDOWN         = 'dropDownList';
    const TYPE_LISTBOX          = 'listBox';
    const TYPE_CHECKBOX_LIST    = 'checkboxList';
    const TYPE_RADIO_LIST       = 'radioList';
    const TYPE_STATIC           = 'static';
    const TYPE_CHECKBOX         = 'checkbox';
    const TYPE_RADIO            = 'radio';

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
     * @var string|\Closure
     */
    public $value;

    /**
     * @var mixed default value for input
     */
    public $defaultValue;

    /**
     * @var array|\Closure items which used for rendering input with multiple choice, e.g. dropDownList. It can be an array
     * or anonymous function with following signature:
     *
     * ```
     *
     * 'columns' => [
     *     ...
     *     [
     *          'name' => 'column',
     *          'items' => function($data) {
     *             // do your magic
     *          }
     *          ....
     *      ]
     * ...
     *
     * ```
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
     * @var bool whether to render inline error for the input. Default to `false`
     */
    public $enableError = false;

    /**
     * @var array the default options for the error tag
     */
    public $errorOptions = ['class' => 'help-block help-block-error'];

    /**
     * @var BaseRenderer the renderer instance
     */
    public $renderer;

    /**
     * @var Model|ActiveRecord|array
     */
    private $_model;


    /**
     * @return Model|ActiveRecord|array
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param Model|ActiveRecord|array $model
     */
    public function setModel($model)
    {
        if ($this->ensureModel($model)) {
            $this->_model = $model;
        }
    }

    protected function ensureModel($model)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
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
     * Prepares the value of column.
     *
     * @return mixed
     */
    protected function prepareValue()
    {
        $data = $this->getModel();
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
     * Returns element id.
     *
     * @param null|int $index
     * @return mixed
     */
    public function getElementId($index = null)
    {
        return $this->normalize($this->getElementName($index));
    }

    /**
     * Returns element's name.
     *
     * @param int|null $index current row index
     * @param bool $withPrefix whether to add prefix.
     * @return string
     */
    abstract public function getElementName($index, $withPrefix = true);

    /**
     * Normalization name.
     *
     * @param $name
     * @return mixed
     */
    private function normalize($name) {
        return str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], strtolower($name));
    }

    /**
     * Renders the input.
     *
     * @param string $name name of the input
     * @param array $options the input options
     * @return string
     */
    public function renderInput($name, $options)
    {
        $options = array_merge($this->options, $options);
        $method = 'render' . Inflector::camelize($this->type);
        $value = $this->prepareValue();

        if (isset($options['items'])) {
            $options['items'] = $this->prepareItems($options['items']);
        }

        if (method_exists($this, $method)) {
            $input = call_user_func_array([$this, $method], [$name, $value, $options]);
        } else {
            $input = $this->renderDefault($name, $value, $options);
        }
        return $input;
    }


    /**
     * Renders drop down list.
     *
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderDropDownList($name, $value, $options)
    {
        Html::addCssClass($options, 'form-control');
        return Html::dropDownList($name, $value, $this->prepareItems($this->items), $options);
    }

    /**
     * Returns the items for list.
     *
     * @param mixed $items
     * @return array|Closure|mixed
     */
    private function prepareItems($items)
    {
        if ($items instanceof \Closure) {
            return call_user_func($items, $this->getModel());
        } else {
            return $items;
        }
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderListBox($name, $value, $options)
    {
        Html::addCssClass($options, 'form-control');
        return Html::listBox($name, $value, $this->prepareItems($this->items), $options);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderHiddenInput($name, $value, $options)
    {
        return Html::hiddenInput($name, $value, $options);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderRadio($name, $value, $options)
    {
        if (!isset($options['label'])) {
            $options['label'] = '';
        }
        if (!array_key_exists('uncheck', $options)) {
            $options['uncheck'] = 0;
        }
        $input = Html::radio($name, $value, $options);
        return Html::tag('div', $input, ['class' => 'radio']);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderRadioList($name, $value, $options)
    {
        if (!array_key_exists('unselect', $options)) {
            $options['unselect'] = '';
        }
        $options['item'] = function ($index, $label, $name, $checked, $value) {
            return '<div class="radio">' . Html::radio($name, $checked, ['label' => $label, 'value' => $value]) . '</div>';
        };
        $input = Html::radioList($name, $value, $this->prepareItems($this->items), $options);
        return Html::tag('div', $input, ['class' => 'radio-list']);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderCheckbox($name, $value, $options)
    {
        if (!isset($options['label'])) {
            $options['label'] = '';
        }
        if (!array_key_exists('uncheck', $options)) {
            $options['uncheck'] = 0;
        }
        $input = Html::checkbox($name, $value, $options);
        return Html::tag('div', $input, ['class' => 'checkbox']);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     */
    protected function renderCheckboxList($name, $value, $options)
    {
        if (!array_key_exists('unselect', $options)) {
            $options['unselect'] = '';
        }
        $options['item'] = function ($index, $label, $name, $checked, $value) {
            return '<div class="checkbox">' . Html::checkbox($name, $checked, ['label' => $label, 'value' => $value]) . '</div>';
        };
        $input = Html::checkboxList($name, $value, $this->prepareItems($this->items), $options);
        return Html::tag('div', $input, ['class' => 'checkbox-list']);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     * @return string
     * @throws InvalidConfigException
     */
    protected function renderDefault($name, $value, $options)
    {
        $type = $this->type;

        if ($type == self::TYPE_STATIC) {
            $input = Html::tag('p', $value, ['class' => 'form-control-static']);
        } elseif (method_exists('yii\helpers\Html', $type)) {
            Html::addCssClass($options, 'form-control');
            $input = Html::$type($name, $value, $options);
        } elseif (class_exists($type) && method_exists($type, 'widget')) {
            $input = $this->renderWidget($type, $name, $value, $options);
        } else {
            throw new InvalidConfigException("Invalid column type '$type'");
        }
        return $input;
    }

    protected function renderWidget($type, $name, $value, $options)
    {
        $model = $this->getModel();
        if ($model instanceof Model) {
            $widgetOptions = [
                'model'     => $model,
                'attribute' => $this->name,
                'value'     => $value,
                'options'   => [
                    'id' => $this->normalize($name),
                    'name' => $name
                ]
            ];
        } else {
            $widgetOptions = [
                'name'  => $name,
                'value' => $value
            ];
        }
        $options = array_merge($options, $widgetOptions);
        if ($options['items'] instanceof \Closure) {
            $options['items'] = call_user_func($options['items'], $this->getModel());
        }
        return $type::widget($options);
    }


    /**
     * @param string $error
     * @return string
     */
    public function renderError($error)
    {
        $options = $this->errorOptions;
        $tag = isset($options['tag']) ? $options['tag'] : 'div';
        $encode = !isset($options['encode']) || $options['encode'] !== false;
        unset($options['tag'], $options['encode']);
        return Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
    }

    /**
     * @param $index
     * @return mixed
     */
    abstract public function getFirstError($index);
}