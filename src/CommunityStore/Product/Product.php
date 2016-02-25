<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Package;
use Page;
use PageType;
use PageTemplate;
use Database;
use File;
use Core;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup as StoreProductUserGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile as StoreProductFile;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreProductValue;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

/**
 * @Entity
 * @Table(name="CommunityStoreProducts")
 */
class Product
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pID;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $cID;

    /**
     * @Column(type="string")
     */
    protected $pName;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $pSKU;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $pDesc;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $pDetail;

    /**
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $pPrice;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pSalePrice;

    /**
     * @Column(type="boolean")
     */
    protected $pFeatured;

    /**
     * @Column(type="integer")
     */
    protected $pQty;

    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $pQtyUnlim;

    /**
     * @Column(type="boolean")
     */
    protected $pNoQty;

    /**
     * @Column(type="integer")
     */
    protected $pTaxClass;

    /**
     * @Column(type="boolean")
     */
    protected $pTaxable;

    /**
     * @Column(type="integer")
     */
    protected $pfID;

    /**
     * @Column(type="boolean")
     */
    protected $pActive;

    /**
     * @Column(type="datetime")
     */
    protected $pDateAdded;

    /**
     * @Column(type="boolean")
     */
    protected $pShippable;

    /**
     * @Column(type="integer")
     */
    protected $pWidth;

    /**
     * @Column(type="integer")
     */
    protected $pHeight;

    /**
     * @Column(type="integer")
     */
    protected $pLength;

    /**
     * @Column(type="integer")
     */
    protected $pWeight;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $pNumberItems;

    /**
     * @Column(type="boolean")
     */
    protected $pCreateUserAccount;

    /**
     * @Column(type="boolean")
     */
    protected $pAutoCheckout;

    /**
     * @Column(type="integer")
     */
    protected $pExclusive;

    /**
     * @Column(type="boolean")
     */
    protected $pVariations;

    // not stored, used for price/sku/etc lookup purposes
    protected $variation;

    public function setVariation($variation)
    {
        if (is_object($variation)) {
            $this->variation = $variation;
        } elseif (is_integer($variation)) {
            $variation = StoreProductVariation::getByID($variation);

            if ($variation) {
                $this->variation = $variation;
            } else {
                $this->variation = null;
            }
        }
    }

    public function removeVariation()
    {
        $this->variation = null;
    }

    public function setInitialVariation()
    {
        if ($this->hasVariations()) {
            $optionGroups = $this->getOptionGroups();
            $optionItems = $this->getOptionItems();
            $optionkeys = array();

            foreach ($optionGroups as $optionGroup) {
                foreach ($optionItems as $option) {
                    if ($option->getProductOptionGroupID() == $optionGroup->getID()) {
                        $optionkeys[] = $option->getID();
                        break;
                    }
                }
            }

            $this->setVariation(StoreProductVariation::getByOptionItemIDs($optionkeys));
        }
    }

    public function getVariation()
    {
        return $this->variation;
    }

    public function setCollectionID($cID)
    {
        $this->cID = $cID;
    }
    public function setName($name)
    {
        $this->pName = $name;
    }
    public function setSKU($sku)
    {
        $this->pSKU = $sku;
    }
    public function setDescription($description)
    {
        $this->pDesc = $description;
    }
    public function setDetail($detail)
    {
        $this->pDetail = $detail;
    }
    public function setPrice($price)
    {
        $this->pPrice = $price;
    }
    public function setSalePrice($price)
    {
        $this->pSalePrice = ($price != '' ? $price : null);
    }
    public function setIsFeatured($bool)
    {
        $this->pFeatured = (!is_null($bool) ? $bool : false);
    }
    public function setQty($qty)
    {
        $this->pQty = ($qty ? $qty : 0);
    }
    public function setIsUnlimited($bool)
    {
        $this->pQtyUnlim = (!is_null($bool) ? $bool : false);
    }
    public function setAllowBackOrder($bool)
    {
        $this->pBackOrder = (!is_null($bool) ? $bool : false);
    }
    public function setNoQty($bool)
    {
        $this->pNoQty = $bool;
    }
    public function setTaxClass($taxClass)
    {
        $this->pTaxClass = $taxClass;
    }
    public function setIsTaxable($bool)
    {
        $this->pTaxable = (!is_null($bool) ? $bool : false);
    }
    public function setImageID($fID)
    {
        $this->pfID = $fID;
    }
    public function setIsActive($bool)
    {
        $this->pActive = $bool;
    }
    public function setDateAdded($date)
    {
        $this->pDateAdded = $date;
    }
    public function setIsShippable($bool)
    {
        $this->pShippable = (!is_null($bool) ? $bool : false);
    }
    public function setWidth($width)
    {
        $this->pWidth = $width;
    }
    public function setHeight($height)
    {
        $this->pHeight = $height;
    }
    public function setLength($length)
    {
        $this->pLength = $length;
    }
    public function setWeight($weight)
    {
        $this->pWeight = $weight;
    }
    public function setNumberItems($number)
    {
        $this->pNumberItems = $number;
    }
    public function setCreatesUserAccount($bool)
    {
        $this->pCreateUserAccount = (!is_null($bool) ? $bool : false);
    }
    public function setAutoCheckout($bool)
    {
        $this->pAutoCheckout = (!is_null($bool) ? $bool : false);
    }
    public function setIsExclusive($bool)
    {
        $this->pExclusive = (!is_null($bool) ? $bool : false);
    }
    public function setHasVariations($bool)
    {
        $this->pVariations = (!is_null($bool) ? $bool : false);
    }

    public function updateProductQty($qty)
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $variation->setVariationQty($qty);
                $variation->save();
            }
        } else {
            $this->setQty($qty);
            $this->save();
        }
    }

    public static function getByID($pID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product', $pID);
    }

    public static function getByCollectionID($cID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product')->findOneBy(array('cID' => $cID));
    }

    public function saveProduct($data)
    {
        if ($data['pID']) {
            //if we know the pID, we're updating.
            $product = self::getByID($data['pID']);
            $product->setPageDescription($data['pDesc']);
        } else {
            //else, we don't know it and we're adding a new product
            $product = new self();
            $dt = Core::make('helper/date');
            $product->setDateAdded(new \Datetime());
        }
        $product->setName($data['pName']);
        $product->setSKU($data['pSKU']);
        $product->setDescription($data['pDesc']);
        $product->setDetail($data['pDetail']);
        $product->setPrice($data['pPrice']);
        $product->setSalePrice($data['pSalePrice']);
        $product->setIsFeatured($data['pFeatured']);
        $product->setQty($data['pQty']);
        $product->setIsUnlimited($data['pQtyUnlim']);
        $product->setAllowBackOrder($data['pBackOrder']);
        $product->setNoQty($data['pNoQty']);
        $product->setTaxClass($data['pTaxClass']);
        $product->setIsTaxable($data['pTaxable']);
        $product->setImageID($data['pfID']);
        $product->setIsActive($data['pActive']);
        $product->setCreatesUserAccount($data['pCreateUserAccount']);
        $product->setIsShippable($data['pShippable']);
        $product->setWidth($data['pWidth']);
        $product->setHeight($data['pHeight']);
        $product->setLength($data['pLength']);
        $product->setWeight($data['pWeight']);
        $product->setNumberItems($data['pNumberItems']);
        $product->setAutoCheckout($data['pAutoCheckout']);
        $product->setIsExclusive($data['pExclusive']);

        // if we have no product groups, we don't have variations to offer
        if (empty($data['pogName'])) {
            $product->setHasVariations(0);
        } else {
            $product->setHasVariations($data['pVariations']);
        }

        $product->save();
        if (!$data['pID']) {
            $product->generatePage($data['selectPageTemplate']);
        }

        return $product;
    }

    public function getID()
    {
        return $this->pID;
    }
    public function getName()
    {
        return $this->pName;
    }
    public function getSKU()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varsku = $variation->getVariationSKU();

                if ($varsku) {
                    return $varsku;
                } else {
                    return $this->pSKU;
                }
            }
        } else {
            return $this->pSKU;
        }
    }
    public function getPageID()
    {
        return $this->cID;
    }
    public function getDesc()
    {
        return $this->pDesc;
    }
    public function getDetail()
    {
        return $this->pDetail;
    }
    public function getPrice()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varprice = $variation->getVariationPrice();

                if ($varprice) {
                    return $varprice;
                } else {
                    return $this->pPrice;
                }
            }
        } else {
            return $this->pPrice;
        }
    }
    public function getFormattedOriginalPrice()
    {
        return StorePrice::format($this->getPrice());
    }
    public function getFormattedPrice()
    {
        return StorePrice::format($this->getActivePrice());
    }

    public function getSalePrice()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varprice = $variation->getVariationSalePrice();
                if ($varprice) {
                    return $varprice;
                } else {
                    return $this->pSalePrice;
                }
            }
        } else {
            return $this->pSalePrice;
        }
    }
    public function getFormattedSalePrice()
    {
        $saleprice = $this->getSalePrice();

        if ($saleprice != '') {
            return StorePrice::format($saleprice);
        }
    }

    public function getActivePrice()
    {
        $salePrice = $this->getSalePrice();
        if ($salePrice != "") {
            return $salePrice;
        } else {
            return $this->getPrice();
        }
    }
    public function getFormattedActivePrice()
    {
        return StorePrice::format($this->getActivePrice());
    }
    public function getTaxClassID()
    {
        return $this->pTaxClass;
    }
    public function getTaxClass()
    {
        return StoreTaxClass::getByID($this->pTaxClass);
    }

    public function isTaxable()
    {
        if ($this->pTaxable == "1") {
            return true;
        } else {
            return false;
        }
    }
    public function isFeatured()
    {
        return $this->pFeatured;
    }
    public function isActive()
    {
        return $this->pActive;
    }
    public function isShippable()
    {
        return $this->pShippable;
    }

    public function getDimensions($whl = null)
    {
        $source = $this;

        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $source = $variation;
        }

        switch ($whl) {
            case "w":
                return $source->pWidth;
                break;
            case "h":
                return $source->pHeight;
                break;
            case "l":
                return $source->pLength;
                break;
            default:
                return $source->pLength."x".$source->pWidth."x".$source->pHeight;
                break;
        }
    }
    public function getWeight()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->getVariationWeight();
        } else {
            return $this->pWeight;
        }
    }
    public function getNumberItems()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->getVariationNumberItems();
        } else {
            return $this->pNumberItems;
        }
    }

    public function getImageID()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $id = $variation->getVariationImageID();
            if (!$id) {
                return $this->pfID;
            } else {
                return $id;
            }
        } else {
            return $this->pfID;
        }
    }
    public function getImageObj()
    {
        if ($this->getImageID()) {
            $fileObj = File::getByID($this->getImageID());

            return $fileObj;
        }
    }

    public function getBaseProductImageID()
    {
        return $this->pfID;
    }

    public function getBaseProductImageObj()
    {
        if ($this->getBaseProductImageID()) {
            $fileObj = File::getByID($this->getBaseProductImageID());

            return $fileObj;
        }
    }

    public function hasDigitalDownload()
    {
        return count($this->getDownloadFiles()) > 0 ? true : false;
    }
    public function getDownloadFiles()
    {
        return StoreProductFile::getFilesForProduct($this);
    }
    public function getDownloadFileObjects()
    {
        return StoreProductFile::getFileObjectsForProduct($this);
    }
    public function createsLogin()
    {
        return (bool) $this->pCreateUserAccount;
    }
    public function allowQuantity()
    {
        return !(bool) $this->pNoQty;
    }
    public function isExclusive()
    {
        return (bool) $this->pExclusive;
    }
    public function hasVariations()
    {
        return (bool) $this->pVariations;
    }
    public function isUnlimited()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->isUnlimited();
        } else {
            return (bool) $this->pQtyUnlim;
        }
    }
    public function autoCheckout()
    {
        return (bool) $this->pAutoCheckout;
    }
    public function allowBackOrders()
    {
        return (bool) $this->pBackOrder;
    }
    public function hasUserGroups()
    {
        return count($this->getUserGroups()) > 0 ? true : false;
    }
    public function getUserGroups()
    {
        return StoreProductUserGroup::getUserGroupsForProduct($this);
    }
    public function getUserGroupIDs()
    {
        return StoreProductUserGroup::getUserGroupIDsForProduct($this);
    }

    public function getImage()
    {
        $fileObj = $this->getImageObj();
        if (is_object($fileObj)) {
            return "<img src='".$fileObj->getRelativePath()."'>";
        }
    }
    public function getImageThumb()
    {
        $fileObj = $this->getImageObj();
        if (is_object($fileObj)) {
            return "<img src='".$fileObj->getThumbnailURL('file_manager_listing')."'>";
        }
    }

    public function getQty()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->getVariationQty();
        } else {
            return $this->pQty;
        }
    }

    public function isSellable()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->isSellable();
        } else {
            if ($this->getQty() > 0 || $this->isUnlimited()) {
                return true;
            } else {
                if ($this->allowBackOrders()) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function getImages()
    {
        return StoreProductImage::getImagesForProduct($this);
    }
    public function getimagesobjects()
    {
        return StoreProductImage::getImageObjectsForProduct($this);
    }
    public function getLocationPages()
    {
        return StoreProductLocation::getLocationsForProduct($this);
    }
    public function getOptionGroups()
    {
        return StoreProductOptionGroup::getOptionGroupsForProduct($this);
    }
    public function getOptionItems($onlyvisible = false)
    {
        return StoreProductOptionItem::getOptionItemsForProduct($this, $onlyvisible);
    }
    public function getGroupIDs()
    {
        return StoreProductGroup::getGroupIDsForProduct($this);
    }
    public function getGroups()
    {
        return StoreProductGroup::getGroupsForProduct($this);
    }
    public function getVariations()
    {
        return StoreProductVariation::getVariationsForProduct($this);
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function remove()
    {
        StoreProductImage::removeImagesForProduct($this);
        StoreProductOptionGroup::removeOptionGroupsForProduct($this);
        StoreProductOptionItem::removeOptionItemsForProduct($this);
        StoreProductFile::removeFilesForProduct($this);
        StoreProductGroup::removeGroupsForProduct($this);
        StoreProductLocation::removeLocationsForProduct($this);
        StoreProductUserGroup::removeUserGroupsForProduct($this);
        StoreProductVariation::removeVariationsForProduct($this);
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
        $page = Page::getByID($this->cID);
        if (is_object($page)) {
            $page->delete();
        }
    }

    public function generatePage($templateID = null)
    {
        $pkg = Package::getByHandle('community_store');
        $targetCID = Config::get('communitystore.productPublishTarget');
        $parentPage = Page::getByID($targetCID);
        $pageType = PageType::getByHandle('store_product');
        $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
        if ($templateID) {
            $pt = PageTemplate::getByID($templateID);
            if (is_object($pt)) {
                $pageTemplate = $pt;
            }
        }
        $productParentPage = $parentPage->add(
            $pageType,
            array(
                'cName' => $this->getName(),
                'pkgID' => $pkg->pkgID,
            ),
            $pageTemplate
        );
        $productParentPage->setAttribute('exclude_nav', 1);

        $this->setPageID($productParentPage->getCollectionID());
        $this->setPageDescription($this->getDesc());
    }
    public function setPageDescription($newDescription)
    {
        $productDescription = strip_tags(trim($this->getDesc()));
        $pageID = $this->getPageID();
        if ($pageID) {
            $productPage = Page::getByID($pageID);
            if (is_object($productPage) && $productPage->getCollectionID() > 0) {
                $pageDescription = trim($productPage->getAttribute('meta_description'));
                // if it's the same as the current product description, it hasn't been updated independently of the product
                if ($pageDescription == '' || $productDescription == $pageDescription) {
                    $productPage->setAttribute('meta_description', strip_tags($newDescription));
                }
            }
        }
    }
    public function setPageID($cID)
    {
        $this->setCollectionID($cID);
        $this->save();
    }

    /* TO-DO
     * This isn't completely accurate as an order status may be incomplete and never change,
     * or an order may be canceled. So at somepoint, circle back to this to check for certain status's
     */
    public function getTotalSold()
    {
        $db = Database::connection();
        $results = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE pID = ?", $this->pID);

        return count($results);
    }

    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }
    public function getAttribute($ak, $displayMode = false)
    {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        if (is_object($ak)) {
            $av = $this->getAttributeValueObject($ak);
            if (is_object($av)) {
                return $av->getValue($displayMode);
            }
        }
    }
    public function getAttributeValueObject($ak, $createIfNotFound = false)
    {
        $db = Database::connection();
        $av = false;
        $v = array($this->getID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne("SELECT avID FROM CommunityStoreProductAttributeValues WHERE pID=? AND akID=?", $v);
        if ($avID > 0) {
            $av = StoreProductValue::getByID($avID);
            if (is_object($av)) {
                $av->setProduct($this);
                $av->setAttributeKey($ak);
            }
        }

        if ($createIfNotFound) {
            $cnt = 0;

            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne("SELECT COUNT(avID) FROM CommunityStoreProductAttributeValues WHERE avID=?", $av->getAttributeValueID());
            }

            if ((!is_object($av)) || ($cnt > 1)) {
                $av = $ak->addAttributeValue();
            }
        }

        return $av;
    }
}
