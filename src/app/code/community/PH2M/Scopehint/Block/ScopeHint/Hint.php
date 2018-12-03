<?php
class PH2M_Scopehint_Block_ScopeHint_Hint extends AvS_ScopeHint_Block_Hint
{

    /**
     * Override - Adding news types
     * @return array
     */
    protected function _getChangedScopesForGlobal()
    {
        $this->getLayout()->getBlock('head')->addJs('scopehint/tooltip.js');

        $changedScopes = [];

        if ($this->getType() === 'config') {
            foreach (Mage::app()->getWebsites() as $website) {

                /** @var Mage_Core_Model_Website $website */
                if ($this->_isValueChanged($website)) {
                    $changedScopes[Mage::helper('scopehint')->__('Website: %s', $website->getName())] = $this->_getReadableConfigValue($website);
                }

                foreach ($website->getStores() as $store) {

                    /** @var Mage_Core_Model_Store $store */
                    if ($this->_isValueChanged($store, $website)) {
                        $changedScopes[Mage::helper('scopehint')->__('Store View: %s', $this->_getFullStoreName($store))] = $this->_getReadableConfigValue($store);
                    }
                }
            }
        } elseif ($this->isScopeHintAllowed($this->getType(), 'all')) {
            foreach (Mage::app()->getStores() as $store) {

                /** @var Mage_Core_Model_Store $store */
                if ($this->_isValueChanged($store)) {
                    $changedScopes[Mage::helper('scopehint')->__('Store View: %s', $this->_getFullStoreName($store))] = $this->_getReadableConfigValue($store);
                }
            }
        }

        return $changedScopes;
    }


    public function isScopeHintAllowed($type, $filter = 'all')
    {
        $allowed = [];
        switch ($filter) {
            case 'all':
                $allowed = $this->getAllEav();
                break;
            case 'standard':
                $allowed = $this->getEavStandard();
                break;
            case 'tree':
                $allowed = $this->getEavTree();
                break;
        }

        return in_array($type, $allowed);
    }

    public function getEavStandard()
    {
        return explode(',', Mage::getStoreConfig('admin/scopehint/eav_standard_list'));
    }

    public function getEavTree()
    {
        return explode(',', Mage::getStoreConfig('admin/scopehint/eav_tree_list'));
    }

    public function getAllEav()
    {
        return array_merge($this->getEavStandard(), $this->getEavTree());
    }

    /**
     * Override - Add value type
     * @param Mage_Core_Model_Store|Mage_Core_Model_Website|null $scope
     * @return string
     */
    protected function _getValue($scope)
    {
        if ($this->getType() === 'config') {
            $configCode = $this->_getConfigCode();

            if (is_null($scope)) {
                return (string)Mage::getConfig()->getNode('default/' . $configCode);
            } elseif ($scope instanceof Mage_Core_Model_Store) {
                return (string)Mage::getConfig()->getNode('stores/' . $scope->getCode() . '/' . $configCode);
            } elseif ($scope instanceof Mage_Core_Model_Website) {
                return (string)Mage::getConfig()->getNode('websites/' . $scope->getCode() . '/' . $configCode);
            }
        } elseif ($this->isScopeHintAllowed($this->getType(), 'standard')) {
            $attributeName = $this->getElement()->getData('name');
            if (is_null($scope)) {
                $value = $this->_getObject()->getData($attributeName);
                if (is_array($value)) {
                    if (is_array($value[0])) {
                        return '';
                    }
                    return implode(',', $value);
                }
                return $value;
            } elseif ($scope instanceof Mage_Core_Model_Store) {
                $value = $this->_getObject($scope)->getData($attributeName);
                if (is_array($value)) {
                    if (is_array($value[0])) {
                        return '';
                    }
                    return implode(',', $value);
                }
                return $value;
            }
        } elseif ($this->isScopeHintAllowed($this->getType(), 'tree')) {
            $attributeName = $this->getElement()->getData('name');
            if (is_null($scope)) {
                $value = $this->_getObject()->getData($attributeName);
                if (is_array($value)) {
                    return implode(',', $value);
                }
                return $value;
            } elseif ($scope instanceof Mage_Core_Model_Store) {
                $value = $this->_getObject($scope)->getData($attributeName);
                if (is_array($value)) {
                    return implode(',', $value);
                }
                return $value;
            }
        }
    }

    protected function _getObject(Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $storeId = 0;
        } else {
            $storeId = $store->getId();
        }

        if (is_null(Mage::registry($this->getType() . '_' . $storeId))) {
            $object = Mage::getModel($this->getEntityModel());
            $object->setStoreId($storeId);
            Mage::register($this->getType() . '_' . $storeId, $object->load($this->getEntityId()));
        }

        return Mage::registry($this->getType() . '_' . $storeId);
    }

    public function getEntityModel()
    {
        if (!$entityModel = $this->getData('entity_model')) {
            if ($this->getType() === 'product') {
                $entityModel = 'catalog/product';
            } elseif ($this->getType() === 'category') {
                $entityModel = 'catalog/category';
            }
        }

        return $entityModel;
    }

    /**
     * @return int|string
     * @throws Exception
     *
     */
    protected function getEntityId()
    {
        if ($this->getType() === 'category') {
            return Mage::registry('current_category')->getId();
        }

        return intval($this->getRequest()->getParam('id'));
    }
}
