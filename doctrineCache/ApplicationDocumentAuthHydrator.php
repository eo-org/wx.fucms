<?php

namespace DoctrineMongoHydrator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class ApplicationDocumentAuthHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate($document, $data, array $hints = array())
    {
        $hydratedData = array();

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['websiteId'])) {
            $value = $data['websiteId'];
            $return = (string) $value;
            $this->class->reflFields['websiteId']->setValue($document, $return);
            $hydratedData['websiteId'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['status'])) {
            $value = $data['status'];
            $return = (string) $value;
            $this->class->reflFields['status']->setValue($document, $return);
            $hydratedData['status'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['authorizerAppid'])) {
            $value = $data['authorizerAppid'];
            $return = (string) $value;
            $this->class->reflFields['authorizerAppid']->setValue($document, $return);
            $hydratedData['authorizerAppid'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['authorizerAccessToken'])) {
            $value = $data['authorizerAccessToken'];
            $return = (string) $value;
            $this->class->reflFields['authorizerAccessToken']->setValue($document, $return);
            $hydratedData['authorizerAccessToken'] = $return;
        }

        /** @Field(type="int") */
        if (isset($data['expiresIn'])) {
            $value = $data['expiresIn'];
            $return = (int) $value;
            $this->class->reflFields['expiresIn']->setValue($document, $return);
            $hydratedData['expiresIn'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['authorizerRefreshToken'])) {
            $value = $data['authorizerRefreshToken'];
            $return = (string) $value;
            $this->class->reflFields['authorizerRefreshToken']->setValue($document, $return);
            $hydratedData['authorizerRefreshToken'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['tokenModified'])) {
            $value = $data['tokenModified'];
            if ($value instanceof \MongoDate) { $date = new \DateTime(); $date->setTimestamp($value->sec); $return = $date; } else { $return = new \DateTime($value); }
            $this->class->reflFields['tokenModified']->setValue($document, clone $return);
            $hydratedData['tokenModified'] = $return;
        }

        /** @Field(type="hash") */
        if (isset($data['funcInfo'])) {
            $value = $data['funcInfo'];
            $return = $value;
            $this->class->reflFields['funcInfo']->setValue($document, $return);
            $hydratedData['funcInfo'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['created'])) {
            $value = $data['created'];
            if ($value instanceof \MongoDate) { $date = new \DateTime(); $date->setTimestamp($value->sec); $return = $date; } else { $return = new \DateTime($value); }
            $this->class->reflFields['created']->setValue($document, clone $return);
            $hydratedData['created'] = $return;
        }
        return $hydratedData;
    }
}