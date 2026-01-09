<?php
    /**
     *
     * Borrowed DataGrid and adapted to own needs.
     * Removed restriction (renderPaginator()) from the renderCaption()function.
     * And it allows using the caption string which can be used for the table.
     * The purpose is to make the table as flexible as possible.
     *
     */

    namespace QCubed\Plugin;

    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use Throwable;
    use QCubed\Control\FormBase;
    use QCubed\Control\TableBase;
    use QCubed\Event\DataGridSort;
    use QCubed\Event\CheckboxColumnClick;
    use QCubed\Action\AjaxControl;
    use QCubed\Action\StopPropagation;
    use QCubed\Project\Application;
    use QCubed\Table\ColumnBase;
    use QCubed\Project\Control\ControlBase;
    use QCubed\Table\DataColumn;
    use QCubed\Table\DataGridCheckboxColumn;
    use QCubed\Type;
    use QCubed\QString;
    use QCubed\Html;

//if (!defined('QCUBED_FONT_AWESOME_CSS')) {
//    define('QCUBED_FONT_AWESOME_CSS', 'https://opensource.keycdn.com/fontawesome/4.6.3/font-awesome.min.css');
//}

    /**
     * Class VauuTable
     *
     * This class is designed primarily to work alongside the code generator, but it can be independent as well. It creates
     * an HTML table that displays data from the database. The data can possibly be sorted by clicking on the header cell
     * of the sort column.
     *
     * This grid also has close ties to the QDataGrid_CheckboxColumn to easily enable the addition of a column or columns
     * of checkboxes.
     *
     * This class is NOT intended to support column filters, but a subclass could be created that could do so. Just don't
     * do that here.
     *
     * @property boolean $RenderAsHeader if true, all cells in the column will be rendered with a tag "th" tag instead of tag "td"
     * @property  string $SortColumnId The id of the currently sorted column. Does not change if columns are re-ordered.
     * @property  int $SortColumnIndex The index of the currently sorted column.
     * @property  int $SortDirection SortAscending or SortDescending.
     * @property  array $SortInfo An array containing the sort data, so you can save and restore it later if needed.
     * @property mixed OrderByClause
     *
     * @package QCubed\Plugin\Control
     */
    class VauuTable extends TableBase
    {
        /** Numbers that can be used to multiply against the results of comparison functions to reverse the order. */
        const int SORT_ASCENDING = 1;
        const int SORT_DESCENDING = -1;

        /** @var boolean */
        protected bool $blnHtmlEntities = true;
        /** @var boolean */
        protected bool $blnRenderAsHeader = false;

        /** @var int Cuter to generate column IDs for columns that do not have them. */
        protected int $intLastColumnId = 0;

        /** @var  string Keeps track of the current sort column. We do it by id so that the table can add/hide/show or rearrange columns and maintain the sort column. */
        protected string $strSortColumnId = '';

        /** @var int The direction of the currently sorted column. */
        protected int $intSortDirection = self::SORT_ASCENDING;

        /**
         * VauuTable constructor.
         *
         * @param ControlBase|FormBase $objParentObject
         * @param string|null $strControlId
         *
         * @throws Caller
         */
        public function __construct(ControlBase|FormBase $objParentObject, ?string $strControlId = null)
        {
            try {
                parent::__construct($objParentObject, $strControlId);

                $this->addCssFile(QCUBED_FONT_AWESOME_CSS);

                $this->addActions();
            } catch (Caller  $objExc) {
                $objExc->incrementOffset();
                throw $objExc;
            }
        }

        /**
         * Returns the caption string which can be used for the table.
         *
         * @return string
         */
        protected function renderCaption(): string
        {
            $strHtml = '';
            if ($this->strCaption) {
                $strHtml .= '<caption>' . QString::htmlEntities($this->strCaption) . '</caption>' . _nl();
            }
            return $strHtml;
        }

        /**
         * Renders the given paginator in a span in the caption. If a caption already exists, it will add the caption.
         *
         * @return string
         * @throws Caller
         */
        protected function renderPaginator(): string
        {
            $objPaginator = $this->objPaginator;
            if (!$objPaginator) {
                return '';
            }

            $strHtml = $objPaginator->render(false);
            $strHtml = Html::renderTag('span', ['class' => 'paginator-control'], $strHtml);
            if ($this->strCaption) {
                $strHtml = '<span>' . QString::htmlEntities($this->strCaption) . '</span>' . $strHtml;
            }

            return Html::renderTag('caption', null, $strHtml);
        }

        /**
         * Adds the actions for the table. Override to add additional actions. If you are detecting clicks
         * that need to cancel the default action, put those in front of this function.
         * @throws Caller
         */
        public function addActions(): void
        {
            $this->addAction(new CheckboxColumnClick(), new AjaxControl($this, 'CheckClick'));
            $this->addAction(new CheckboxColumnClick(),
                new StopPropagation()); // prevent check click from bubbling as a row click.

            $this->addAction(new DataGridSort(), new AjaxControl($this, 'SortClick'));
            $this->addAction(new DataGridSort(), new StopPropagation());   // in case datagrid is nested
        }

        /**
         * Adds a column at the specified index and ensures the column has a unique ID assigned.
         *
         * @param int $intColumnIndex The index at which the column should be added.
         * @param ColumnBase $objColumn The column object to be added.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        public function addColumnAt(int $intColumnIndex, ColumnBase $objColumn): void
        {
            parent::addColumnAt($intColumnIndex, $objColumn);
            // Make sure the column has an Id, since we use that to track sorting.
            if (!$objColumn->Id) {
                $objColumn->Id = $this->ControlId . '_col_' . $this->intLastColumnId++;
            }
        }

        /**
         * Handles the click event on a DataGrid column, processing the action for a checkbox column.
         *
         * @param string $strFormId The ID of the form in which the DataGrid is included.
         * @param string $strControlId The ID of the control being clicked.
         * @param array $strParameter An associative array containing parameters for the click event, such as 'col' for the column index.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function checkClick(string $strFormId, string $strControlId, array $strParameter): void
        {
            $intColumnIndex = $strParameter['col'];
            $objColumn = $this->getColumn($intColumnIndex, true);

            if ($objColumn instanceof DataGridCheckboxColumn) {
                $objColumn->click($strParameter);
            }
        }

        /**
         * Clears the checked items for DataGridCheckboxColumns. If a column ID is provided,
         * only the checkbox column with the matching ID will have its checked items cleared.
         * Otherwise, all checkbox columns will be cleared.
         *
         * @param string|null $strColId The optional ID of the checkbox column to clear.
         *                              If null, all checkbox columns are cleared.
         *
         * @return void
         */
        public function clearCheckedItems(?string $strColId = null): void
        {
            foreach ($this->objColumnArray as $objColumn) {
                if ($objColumn instanceof DataGridCheckboxColumn) {
                    if (is_null($strColId) || $objColumn->Id === $strColId) {
                        $objColumn->clearCheckedItems();
                    }
                }
            }
        }

        /**
         * Retrieves the IDs of checked items from a DataGridCheckboxColumn.
         *
         * @param string|null $strColId The ID of the column to retrieve checked item IDs for. If null, the first matching column is used.
         *
         * @return array|null An array of IDs of the checked items, or null if no matching column is found.
         */
        public function getCheckedItemIds(?string $strColId = null): ?array
        {
            foreach ($this->objColumnArray as $objColumn) {
                if ($objColumn instanceof DataGridCheckboxColumn) {
                    if (is_null($strColId) ||
                        $objColumn->Id === $strColId
                    ) {
                        return $objColumn->getCheckedItemIds();
                    }
                }
            }
            return null; // column not found
        }

        /**
         * Handles the click event for sorting columns in a data table.
         * Modifies a sorting direction and resets pagination if applicable.
         *
         * @param string $strFormId The ID of the form that triggered the event.
         * @param string $strControlId The ID of the control that triggered the event.
         * @param mixed $mixParameter The parameter indicating the column index to sort.
         *
         * @throws Caller
         * @throws InvalidCast
         */
        protected function sortClick(string $strFormId, string $strControlId, mixed $mixParameter): void
        {
            $intColumnIndex = Type::cast($mixParameter, Type::INTEGER);
            $objColumn = $this->getColumn($intColumnIndex, true);

            if (!$objColumn) {
                return;
            }
            assert($objColumn instanceof DataColumn);

            $this->blnModified = true;

            $strId = $objColumn->Id;

            // Reset pagination (if applicable)
            if ($this->objPaginator) {
                $this->PageNumber = 1;
            }

            // Make sure the Column is Sortable
            if ($objColumn->OrderByClause) {
                // It is

                // Are we currently sorting by this column?
                if ($this->strSortColumnId === $strId) {
                    // Yes, we are currently sorting by this column.

                    // In Reverse?
                    if ($this->intSortDirection == self::SORT_DESCENDING) {
                        // Yep -- reverse the sort
                        $this->intSortDirection = self::SORT_ASCENDING;
                    } else {
                        // Nope -- can we reverse?
                        if ($objColumn->ReverseOrderByClause) {
                            $this->intSortDirection = self::SORT_DESCENDING;
                        }
                    }
                } else {
                    // Nope -- so let's set it to this column
                    $this->strSortColumnId = $strId;
                    $this->intSortDirection = self::SORT_ASCENDING;
                }
            } else {
                // It isn't -- clear all-sort properties
                $this->intSortDirection = self::SORT_ASCENDING;
                $this->strSortColumnId = null;
            }
        }

        /**
         * Generates and returns the HTML for the header row(s) of a table.
         *
         * This method constructs the header rows based on the columns defined in
         * the table and their associated properties, such as visibility, parameters,
         * and sorting capabilities.
         *
         * @return string The HTML string representing the header row(s) of a table.
         */
        protected function getHeaderRowHtml(): string
        {
            $strToReturn = '';
            for ($i = 0; $i < $this->intHeaderRowCount; $i++) {
                $this->intCurrentHeaderRowIndex = $i;

                $strCells = '';
                if ($this->objColumnArray) {
                    foreach ($this->objColumnArray as $objColumn) {
                        assert ($objColumn instanceof DataColumn);
                        if ($objColumn->Visible) {
                            $strCellValue = $this->getHeaderCellContent($objColumn);
                            $aParams = $objColumn->getHeaderCellParams();
                            $aParams['id'] = $objColumn->Id;
                            if ($objColumn->OrderByClause) {
                                if (isset($aParams['class'])) {
                                    $aParams['class'] .= ' ' . 'sortable';
                                } else {
                                    $aParams['class'] = 'sortable';
                                }
                            }
                            $strCells .= Html::renderTag('th', $aParams, $strCellValue);
                        }
                    }
                }
                $strToReturn .= Html::renderTag('tr', $this->getHeaderRowParams(), $strCells);
            }

            return $strToReturn;
        }

        /**
         * Generates the content for a header cell in the data grid based on the provided column.
         *
         * @param DataColumn $objColumn The DataColumn object representing the current column header to process.
         *
         * @return string The HTML content for the header cell, including sort indicators and wrapping for accessibility.
         */
        protected function getHeaderCellContent(DataColumn $objColumn): string
        {
            $blnSortable = false;
            $strCellValue = $objColumn->fetchHeaderCellValue();
            if ($objColumn->HtmlEntities) {
                $strCellValue = QString::htmlEntities($strCellValue);
            }
            $strCellValue = Html::renderTag('span', null, $strCellValue);    // wrap in a span for positioning

            if ($this->strSortColumnId == $objColumn->Id) {
                if ($this->intSortDirection == self::SORT_ASCENDING) {
                    $strCellValue = $strCellValue . ' ' . Html::renderTag('i', ['class' => 'fa fa-sort-desc fa-lg']);
                } else {
                    $strCellValue = $strCellValue . ' ' . Html::renderTag('i', ['class' => 'fa fa-sort-asc fa-lg']);
                }
                $blnSortable = true;
            } else {
                if ($objColumn->OrderByClause) {    // sortable, but not currently being sorted
                    $strCellValue = $strCellValue . ' ' . Html::renderTag('i',
                            ['class' => 'fa fa-sort fa-lg']);
                    $blnSortable = true;
                }
            }

            if ($blnSortable) {
                // Wrap the header cell in a html5 block-link to help with assistive technologies.
                $strCellValue = Html::renderTag('div', null, $strCellValue);
                $strCellValue = Html::renderTag('a', ['href' => 'javascript:;'],
                    $strCellValue); // this action will be handled by qcubed.js click handler in qcubed.datagrid2()
            }

            return $strCellValue;
        }

        /**
         * Initializes and creates the jQuery widget for the control
         * and executes necessary JavaScript functionality.
         *
         * @return void
         */
        protected function makeJqWidget(): void
        {
            parent::makeJqWidget();
            Application::executeJsFunction('qcubed.datagrid2', $this->ControlId);
        }


        /**
         * Retrieves the current state of the component, including sorting and pagination details.
         *
         * @return array|null An associative array representing the state with keys for sort column, sort direction, and page number.
         */
        public function getState(): ?array
        {
            $state = array();
            $state["c"] = $this->strSortColumnId;
            $state["d"] = $this->intSortDirection;
            if ($this->Paginator || $this->PaginatorAlternate) {
                $state["p"] = $this->PageNumber;
            }
            return $state;
        }

        /**
         * Updates the internal state of the object using the provided state array.
         * Ensures that a sorting direction is restricted to defined constants and updates pagination if applicable.
         *
         * @param mixed $state An associative array representing the state, where possible keys are:
         *                     'c' - the column identifier for sorting.
         *                     'd' - the sorting direction (integer, acceptable values defined by class constants).
         *                     'p' - the current page number for pagination.
         *
         * @return void
         */
        public function putState(mixed $state): void
        {
            // use the name as the column key because columns might be added or removed for some reason
            if (isset($state["c"])) {
                $this->strSortColumnId = $state["c"];
            }
            if (isset($state["d"])) {
                $this->intSortDirection = $state["d"];
                if ($this->intSortDirection != self::SORT_DESCENDING) {
                    $this->intSortDirection = self::SORT_ASCENDING;    // make sure it's only one of two values
                }
            }
            if (isset($state["p"]) &&
                ($this->Paginator || $this->PaginatorAlternate)
            ) {
                $this->PageNumber = $state["p"];
            }
        }

        /**
         * Returns the index of the currently sorted column.
         * Returns false if nothing is selected.
         *
         * @return bool|int
         */
        public function getSortColumnIndex(): bool|int
        {
            if ($this->objColumnArray && ($count = count($this->objColumnArray))) {
                for ($i = 0; $i < $count; $i++) {
                    if ($this->objColumnArray[$i]->Id == $this->SortColumnId) {
                        return $i;
                    }
                }
            }
            return false;
        }

        /**
         * Retrieves the order-by-clause information based on the current sorting column and direction.
         *
         * @return mixed The order by a clause, the reverse order by clause if applicable, or null if no valid sorting information is available.
         */
        public function getOrderByInfo(): mixed
        {
            if (!($this->strSortColumnId == null)) {
                $objColumn = $this->getColumnById($this->strSortColumnId);
                assert($objColumn instanceof DataColumn);
                if ($objColumn->OrderByClause) {
                    if ($this->intSortDirection == self::SORT_ASCENDING) {
                        return $objColumn->OrderByClause;
                    } else {
                        if ($objColumn->ReverseOrderByClause) {
                            return $objColumn->ReverseOrderByClause;
                        } else {
                            return $objColumn->OrderByClause;
                        }
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        /**
         * Magic method to retrieve the value of a property based on its name.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed Returns the value of the specified property. May return various types based on the property.
         * @throws Caller Throws exception if the property does not exist or cannot be retrieved.
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'HtmlEntities': return $this->blnHtmlEntities;
                case "OrderByClause": return $this->getOrderByInfo();
                case "SortColumnId": return $this->strSortColumnId;
                case "SortDirection": return $this->intSortDirection;
                case "SortColumnIndex": return $this->getSortColumnIndex();
                case "SortInfo": return ['id' => $this->strSortColumnId, 'dir' => $this->intSortDirection];

                default:
                    try {
                        return parent::__get($strName);
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }

        /**
         * Magic method to set properties dynamically. Handles setting of various
         * predefined properties with type checking and validation.
         *
         * @param string $strName The name of the property to set.
         * @param mixed $mixValue The value to assign to the specified property.
         *
         * @return void
         *
         * @throws Caller Thrown if the property is not recognized or cannot be handled by the parent class.
         * @throws InvalidCast Thrown if the provided value cannot be cast to the expected type.
         * @throws Throwable
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case "HtmlEntities":
                    try {
                        $this->blnHtmlEntities = Type::cast($mixValue, Type::BOOLEAN);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case "SortColumnId":
                    try {
                        $this->strSortColumnId = Type::cast($mixValue, Type::STRING);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case "SortColumnIndex":
                    try {
                        $intIndex = Type::cast($mixValue, Type::INTEGER);
                        if ($intIndex < 0) {
                            $intIndex = 0;
                        }
                        if ($intIndex < count($this->objColumnArray)) {
                            $objColumn = $this->objColumnArray[$intIndex];
                        } elseif (count($this->objColumnArray) > 0) {
                            $objColumn = end($this->objColumnArray);
                        } else {
                            // no columns
                            $objColumn = null;
                        }
                        if ($objColumn && $objColumn->OrderByClause) {
                            $this->strSortColumnId = $objColumn->Id;
                        }
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                    break;

                case "SortDirection":
                    try {
                        $this->intSortDirection = Type::cast($mixValue, Type::INTEGER);
                        if ($this->intSortDirection != self::SORT_DESCENDING) {
                            $this->intSortDirection = self::SORT_ASCENDING;    // make sure it's only one of two values
                        }
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case "SortInfo":    // restore the SortInfo obtained from the getter
                    try {
                        if (isset($mixValue['id']) && isset($mixValue['dir'])) {
                            $this->intSortDirection = Type::cast($mixValue['dir'], Type::INTEGER);
                            $this->strSortColumnId = Type::cast($mixValue['id'], Type::STRING);
                        }
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        parent::__set($strName, $mixValue);
                        break;
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }
    }
