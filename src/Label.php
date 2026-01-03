<?php

namespace QCubed\Plugin;

use QCubed\Control\BlockControl;

/**
 * Class Label
 *
 * Converts\QCubed\Control\Label to a drawing boot strategy according to the client's a desired theme.
 * @package QCubed\Plugin
 */
class Label extends \QCubed\Control\Label
{
    protected string $strCssClass = "control-label";
    protected string $strTagName = "label";
    protected bool $blnRequired = false;

    /**
     * Retrieves the inner HTML content for the current block control.
     * If the control is marked as required, appends a required indicator to the returned content.
     *
     * @return string The inner HTML content, with an appended required indicator if applicable.
     */
    protected function getInnerHtml(): string
    {
        $strToReturn = BlockControl::getInnerHtml();
        if ($this->blnRequired) {
            $strToReturn = $strToReturn . '<span class="required" aria-required="true"> * </span>';
        }
        return $strToReturn;
    }
}