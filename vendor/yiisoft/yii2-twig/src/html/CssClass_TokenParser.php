<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\twig\html;

class CssClass_TokenParser extends BaseCss_TokenParser
{
    public function getNodeClass()
    {
        return '\yii\twig\html\CssClassNode';
    }

    public function getTag()
    {
        return 'css_class';
    }
}
