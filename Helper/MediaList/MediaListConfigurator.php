<?php

namespace Kunstmaan\MediaBundle\Helper\MediaList;

use Kunstmaan\AdminListBundle\AdminList\AbstractAdminListConfigurator;
use Kunstmaan\AdminListBundle\AdminList\AdminListFilter;
use Kunstmaan\AdminListBundle\AdminList\FilterDefinitions\StringFilterType;
use Kunstmaan\AdminListBundle\AdminList\FilterDefinitions\BooleanFilterType;

class MediaListConfigurator extends AbstractAdminListConfigurator
{

    public function buildFilters(AdminListFilter $builder)
    {
        $builder->add('name', new StringFilterType("name"), "form.name");
        $builder->add('classtype', new StringFilterType("classtype"), "form.type");
    }

    public function buildFields()
    {
        $this->addField("name", "form.name", TRUE);
        $this->addField("classtype", "form.type", TRUE);
        $this->addField("createdAt", "form.createdat", TRUE);
        $this->addField("updatedAt", "form.updatedat", TRUE);
    }

    public function getAddUrlFor($params = array())
    {
        return array(
            'image' => array(
                'path'   => 'KunstmaanMediaBundle_folder_imagecreate',
                'params' => array(
                    'gallery_id' => $params['gallery_id']
                )
            ),
            'file'  => array(
                'path'   => 'KunstmaanMediaBundle_folder_filecreate',
                'params' => array(
                    'gallery_id' => $params['gallery_id']
                )
            ),
            'slide' => array(
                'path'   => 'KunstmaanMediaBundle_folder_slidecreate',
                'params' => array(
                    'gallery_id' => $params['gallery_id']
                )
            ),
            'video' => array(
                'path'   => 'KunstmaanMediaBundle_folder_videocreate',
                'params' => array(
                    'gallery_id' => $params['gallery_id']
                )
            )
        );
    }

    public function getEditUrlFor($item)
    {
        return array(
            'path'   => 'KunstmaanMediaBundle_media_show',
            'params' => array(
                'media_id' => $item->getId()
            )
        );
    }

    public function getRepositoryName()
    {
        return 'KunstmaanMediaBundle:Media';
    }

    function adaptQueryBuilder($querybuilder, $params = array())
    {
        parent::adaptQueryBuilder($querybuilder, $params);
        $querybuilder->andwhere($querybuilder->expr()->eq("b.gallery", $params['gallery']));
    }

    function getDeleteUrlFor($item)
    {
        // TODO: Implement getDeleteUrlFor() method.
    }
}
