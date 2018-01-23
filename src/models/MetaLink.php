<?php
/**
 * SEOmatic plugin for Craft CMS 3.x
 *
 * A turnkey SEO implementation for Craft CMS that is comprehensive, powerful,
 * and flexible
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\seomatic\models;

use nystudio107\seomatic\Seomatic;
use nystudio107\seomatic\base\MetaItem;
use nystudio107\seomatic\helpers\ArrayHelper;
use nystudio107\seomatic\helpers\MetaValue as MetaValueHelper;

use Craft;

use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.0.0
 */
class MetaLink extends MetaItem
{
    // Constants
    // =========================================================================

    const ITEM_TYPE = 'MetaLink';

    const UNIQUEKEYS_TAGS = [
        'alternate',
    ];

    // Static Methods
    // =========================================================================

    /**
     * @param array $config
     *
     * @return null|MetaLink
     */
    public static function create(array $config = [])
    {
        $model = null;
        foreach ($config as $key => $value) {
            ArrayHelper::rename($config, $key, Inflector::variablize($key));
        }
        $model = new MetaLink($config);

        return $model;
    }

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $crossorigin;

    /**
     * @var string
     */
    public $href;

    /**
     * @var string
     */
    public $hreflang;

    /**
     * @var string
     */
    public $media;

    /**
     * @var string
     */
    public $rel;

    /**
     * @var string
     */
    public $sizes;

    /**
     * @var string
     */
    public $type;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->key)) {
            $this->key = $this->rel;
            // Unique keys for specific tags
            if (in_array($this->rel, self::UNIQUEKEYS_TAGS)) {
                $this->uniqueKeys = true;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            [['crossorigin', 'href', 'hreflang', 'media', 'rel', 'sizes', 'type'], 'string'],
            ['crossorigin', 'in', 'range' => [
                'anonymous',
                'use-credentials'
            ]],
            ['href', 'url'],
            ['hreflang', 'string'],
            ['rel', 'required'],
            ['rel', 'in', 'range' => [
                'alternate',
                'author',
                'canonical',
                'creator',
                'dns-prefetch',
                'help',
                'icon',
                'license',
                'next',
                'pingback',
                'preconnect',
                'prefetch',
                'preload',
                'prerender',
                'prev',
                'publisher',
                'search',
                'stylesheet',
            ]],
        ]);

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        if ($this->scenario === 'default') {
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function prepForRender(&$data): bool
    {
        $shouldRender = parent::prepForRender($data);
        if ($shouldRender) {
            $scenario = $this->scenario;
            $this->setScenario('render');
            $data = $this->tagAttributes();
            $this->setScenario($scenario);
            MetaValueHelper::parseArray($data);
            // Only render if there's more than one attribute
            if (count($data) > 1) {
                // Special-case scenarios
                if (Seomatic::$devMode) {
                }
            } else {
                $error = Craft::t(
                    'seomatic',
                    '{tagtype} tag `{key}` did not render because it is missing attributes.',
                    ['tagtype' => 'Link', 'key' => $this->key]
                );
                Craft::error($error, __METHOD__);
                $shouldRender = false;
            }
        }

        return $shouldRender;
    }

    /**
     * @inheritdoc
     */
    public function render($params = []):string
    {
        $html = '';
        $options = $this->tagAttributes();
        if ($this->prepForRender($options)) {
            $html = Html::tag('link', '', $options);
        }

        return $html;
    }
}