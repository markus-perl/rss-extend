<?php
namespace RssExtend\Helper;
use Zend\View\Helper\AbstractHelper;


class HeaderButton extends AbstractHelper
{
    const LEFT = 'ui-btn-left';

    const RIGHT = 'ui-btn-right';

    /**
     * @param string $text
     * @param string $url
     * @param string $icon
     * @param string $position
     */
    public function __invoke($text, $url, $icon, $position, array $options = array())
    {
        $optionsText = '';
        foreach ($options as $key => $value)
        {
            $optionsText .=  ' ' . $key . '= "' . $value . '"';
        }

        return '<a class="' . $position . '" href="' . $url . '" ' . $optionsText . ' data-icon="' . $icon . '">' . $text . '</a>';
    }

}