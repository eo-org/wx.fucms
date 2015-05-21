<?php

namespace DoctrineMongoHydrator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class WxDocumentUserHydrator implements HydratorInterface
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
        if (isset($data['openid'])) {
            $value = $data['openid'];
            $return = (string) $value;
            $this->class->reflFields['openid']->setValue($document, $return);
            $hydratedData['openid'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['nickname'])) {
            $value = $data['nickname'];
            $return = (string) $value;
            $this->class->reflFields['nickname']->setValue($document, $return);
            $hydratedData['nickname'] = $return;
        }

        /** @Field(type="int") */
        if (isset($data['sex'])) {
            $value = $data['sex'];
            $return = (int) $value;
            $this->class->reflFields['sex']->setValue($document, $return);
            $hydratedData['sex'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['city'])) {
            $value = $data['city'];
            $return = (string) $value;
            $this->class->reflFields['city']->setValue($document, $return);
            $hydratedData['city'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['country'])) {
            $value = $data['country'];
            $return = (string) $value;
            $this->class->reflFields['country']->setValue($document, $return);
            $hydratedData['country'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['province'])) {
            $value = $data['province'];
            $return = (string) $value;
            $this->class->reflFields['province']->setValue($document, $return);
            $hydratedData['province'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['language'])) {
            $value = $data['language'];
            $return = (string) $value;
            $this->class->reflFields['language']->setValue($document, $return);
            $hydratedData['language'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['headimgurl'])) {
            $value = $data['headimgurl'];
            $return = (string) $value;
            $this->class->reflFields['headimgurl']->setValue($document, $return);
            $hydratedData['headimgurl'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['subscribe_time'])) {
            $value = $data['subscribe_time'];
            $return = (string) $value;
            $this->class->reflFields['subscribe_time']->setValue($document, $return);
            $hydratedData['subscribe_time'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['unionid'])) {
            $value = $data['unionid'];
            $return = (string) $value;
            $this->class->reflFields['unionid']->setValue($document, $return);
            $hydratedData['unionid'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['remark'])) {
            $value = $data['remark'];
            $return = (string) $value;
            $this->class->reflFields['remark']->setValue($document, $return);
            $hydratedData['remark'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['groupid'])) {
            $value = $data['groupid'];
            $return = (string) $value;
            $this->class->reflFields['groupid']->setValue($document, $return);
            $hydratedData['groupid'] = $return;
        }
        return $hydratedData;
    }
}