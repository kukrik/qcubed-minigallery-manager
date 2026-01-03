<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\Click;
    use QCubed\Exception\Caller;

    /**
     * Class DeleteClick
     *
     * Extends the Click class to handle specific functionality involving a delete event.
     * Provides logic for determining a return parameter based on a given selector
     * and event conditions.
     *
     *  Fires when clicking on an element (or its children) that matches
     *  a developer-defined selector and has:
     *   - data-event="delete"
     *   - data-id
     *
     *  Returns data-id as ActionParameter.
     */
    class DeleteClick extends Click
    {
        protected string $strReturnParam;
        protected ?string $strSelector = null;

        /**
         * Constructor method.
         *
         * @param int|null $intDelay The delay parameter, default is 0.
         * @param string|null $strCondition The condition for the event, default, is null.
         * @param string|null $strSelector The CSS selector, default is null.
         * @param bool|null $blnBlockOtherEvents Whether to block other events, default is false.
         *
         * @throws Caller
         */
        public function __construct(?int $intDelay = 0, ?string $strCondition = null, ?string $strSelector = null, ?bool $blnBlockOtherEvents = false)
        {
            $this->strSelector = $strSelector;

            parent::__construct($intDelay, $strCondition, $strSelector, $blnBlockOtherEvents);

            $this->strReturnParam = <<<JS
(function () {
    // find the closest matching element, regardless of click depth
    var \$el = \$j(event.target).closest('$this->strSelector');
    if (!\$el.length) return null;

    // semantic guard
    if (\$el.data('event') !== 'delete') return null;

    // required identifier
    var id = \$el.data('id');
    if (id === undefined || id === null || id === '') return null;

    return id;
})()
JS;
        }

        /**
         * Magic getter method.
         *
         * @param string $strName The name of the property to access.
         *
         * @return mixed The value of the requested property.
         *
         * @throws \Exception If the property does not exist or cannot be accessed.
         * @throws Caller
         */
        public function __get(string $strName): mixed
        {
            if ($strName === 'JsReturnParam') {
                return $this->strReturnParam;
            }
            return parent::__get($strName);
        }
    }
