PH2M Scope Hint
==================
Override AvS/ScopeHint Magento module to allow every custom EAV object

Installation
---------
Add our repository in your `composer.json` (until we added it in Packagist)
```
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/PH2M/Scopehint"
    }
  ]
 ```
Composer :
```
composer require ph2m/scopehint
```
    
Usage
------
1) Add your custom eav type in config

```
<default>
    <admin>
        <scopehint>
            <eav_standard_list>product,myentitytype</eav_standard_list>
            <eav_tree_list>category,myentitytypeintreemode</eav_tree_list>
        </scopehint>
    </admin>
</default>

```

2) Add an event at the end of `getScopeLabel` method localized in your custom Renderer Fieldset Element.
```
$htmlObject = new Varien_Object(['html' => $html]);
Mage::dispatchEvent('adminhtml_renderer_fieldset_element', [
    'html_object' => $htmlObject,
    'element' => $this->getElement(),
    'entity_model' => Mage::getModel('eav/config')->getEntityType($attribute->getEntityTypeId())->getEntityModel(),
]);
$html = $htmlObject->getHtml();
```

Example in the file `Namespace_Module_Block_Adminhtml_Object_Renderer_Fieldset_Element`

```
public function getScopeLabel()
{
    $html      = '';
    $attribute = $this->getElement()->getEntityAttribute();
    if (!$attribute || Mage::app()->isSingleStoreMode()) {
        return $html;
    }
    if ($this->isScopeGlobal($attribute)) {
        $html .= Mage::helper('core')->__('[GLOBAL]');
    } elseif ($this->isScopeWebsite($attribute)) {
        $html .= Mage::helper('core')->__('[WEBSITE]');
    } elseif ($this->isScopeStore($attribute)) {
        $html .= Mage::helper('core')->__('[STORE VIEW]');
    }

    $htmlObject = new Varien_Object(['html' => $html]);
    Mage::dispatchEvent('adminhtml_renderer_fieldset_element', [
        'html_object' => $htmlObject,
        'element' => $this->getElement(),
        'entity_model' => Mage::getModel('eav/config')->getEntityType($attribute->getEntityTypeId())->getEntityModel(),
    ]);
    $html = $htmlObject->getHtml();
    
    return $html;
}
```


Licence
-------
No Licence