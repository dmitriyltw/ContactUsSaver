<?php

namespace Polushkin\ContactUsSaver\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Polushkin\ContactUsSaver\Api\EnquiryRepositoryInterface;
use Polushkin\ContactUsSaver\Api\Data\EnquiryInterface;
use Polushkin\ContactUsSaver\Api\Data\EnquiryInterfaceFactory;
use Polushkin\ContactUsSaver\Model\ResourceModel\Enquiry as ResourceEnquiry;
use Polushkin\ContactUsSaver\Model\ResourceModel\Enquiry\CollectionFactory as EnquiryCollectionFactory;

class EnquiryRepository implements EnquiryRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];
    /**
     * @var ResourceEnquiry
     */
    protected $resource;

    /**
     * @var EnquiryCollectionFactory
     */
    protected $enquiryCollectionFactory;

    /**
     * @var EnquiryInterfaceFactory
     */
    protected $enquiryInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceEnquiry $resource,
        EnquiryCollectionFactory $enquiryCollectionFactory,
        EnquiryInterfaceFactory $enquiryInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->enquiryCollectionFactory = $enquiryCollectionFactory;
        $this->enquiryInterfaceFactory = $enquiryInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param EnquiryInterface $enquiry
     * @return EnquiryInterface
     * @throws CouldNotSaveException
     */
    public function save(EnquiryInterface $enquiry)
    {
        try {
            /** @var EnquiryInterface|\Magento\Framework\Model\AbstractModel $enquiry */
            $this->resource->save($enquiry);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the enquiry: %1',
                $exception->getMessage()
            ));
        }
        return $enquiry;
    }

    /**
     * Get enquiry record
     *
     * @param $enquiryId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($enquiryId)
    {
        if (!isset($this->instances[$enquiryId])) {
            /** @var \Polushkin\ContactUsSaver\Api\Data\EnquiryInterface|\Magento\Framework\Model\AbstractModel $enquiry */
            $enquiry = $this->enquiryInterfaceFactory->create();
            $this->resource->load($enquiry, $enquiryId);
            if (!$enquiry->getId()) {
                throw new NoSuchEntityException(__('Requested enquiry doesn\'t exist'));
            }
            $this->instances[$enquiryId] = $enquiry;
        }
        return $this->instances[$enquiryId];
    }

    /**
     * @param EnquiryInterface $enquiry
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(EnquiryInterface $enquiry)
    {
        /** @var \Polushkin\ContactUsSaver\Api\Data\EnquiryInterface|\Magento\Framework\Model\AbstractModel $enquiry */
        $id = $enquiry->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($enquiry);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove enquiry %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $enquiryId
     * @return bool
     */
    public function deleteById($enquiryId)
    {
        $enquiry = $this->getById($enquiryId);
        return $this->delete($enquiry);
    }
}
