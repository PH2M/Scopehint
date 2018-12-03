<?php
class PH2M_Scopehint_Model_Observer
{
    public function addToolTip($observer)
    {
        $html = $observer->getHtmlObject()->getHtml();
        $html .= '<div class="scopehint" style="padding: 6px 6px 0 6px; display: inline-block;">';
        $html .= $this->_getScopeHintHtml($observer->getEvent()->getElement(), $observer->getEvent()->getEntityModel());
        $html .= '</div>';

        $observer->getEvent()->getHtmlObject()->setHtml($html);
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @param String $entityModel
     * @return string
     */
    protected function _getScopeHintHtml(Varien_Data_Form_Element_Abstract $element, $entityModel)
    {
        list($type, ) = explode('/', $entityModel);

        return Mage::app()->getLayout()
            ->createBlock('scopehint/hint', 'scopehint')
            ->setElement($element)
            ->setType($type)
            ->setEntityModel($entityModel)
            ->toHtml();
    }
}
