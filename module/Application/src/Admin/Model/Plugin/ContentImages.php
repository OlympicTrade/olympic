<?php
namespace ApplicationAdmin\Model\Plugin;

use Aptero\Db\Plugin\Image;
use Aptero\Db\Plugin\Images;
use Aptero\File\Image as ApteroImage;

class ContentImages extends Images
{
    public function save($transaction = false)
    {
        if(empty($this->imagesNew)) {
            return true;
        }

        foreach($this->imagesNew as $image) {
            $imageObj = new ApteroImage();
            $imageObj->setFileName(\Aptero\String\StringFn::randomString());
            $imageObj->setFilePath(PUBLIC_DIR . $image['filename']);
            $imageObj->setResultDirPath($fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder);

            $fileName = $imageObj->save();

            $insert = $this->insert();
            $insert->values(array(
                'filename' => $fileName,
                'desc'     => $image['desc'],
                'sort'     => $image['sort'],
                $this->parentFiled => $this->getParentId(),
            ));

            $this->execute($insert);
        }

        return true;
    }

    /**
     * @param $data
     * @return Image
     */
    public function fill($data)
    {
        $this->images = [];
        foreach($data as $row) {
            $this->images[] = array(
                'filename'  => $row['filename'],
                'desc'      => $row['desc'],
                'sort'      => $row['sort'],
                'id'        => $row['id'],
            );
        }

        $this->loaded = true;

        return $this;
    }

    public function serializeArray($result = [], $prefix = '')
    {
        $data = [];

        foreach ($this as $image) {
            $data[] = [
                'path'  => $image->getImage('a')
            ];
        }

        $result[$prefix . 'images'] = $data;

        return $result;
    }

    /**
     * @param $data
     * @return bool
     */
    public function unserializeArray($data)
    {
        if(empty($data) || empty($data['images'])) {
            return true;
        }

        $data = $data['images'];

        if(isset($data['del'])) {
            foreach($this as $image) {
                if(in_array($image->getId(), $data['del'])) {
                    $image->remove();
                }
            }
        }

        if(isset($data['add'])) {
            $dataToAdd = $data['add'];
            for($i = 0; $i < count($dataToAdd['path']); $i++) {
                $this->imagesNew[] = [
                    'filename'  => urldecode($dataToAdd['path'][$i]),
                    'desc'      => $dataToAdd['desc'][$i],
                    'sort'      => $dataToAdd['sort'][$i],
                ];
            }
        }

        return true;
    }

    public function current()
    {
        $imageData = current($this->images);

        $image = new Image();
        $image->setTable($this->table());
        $image->setFolder($this->folder);
        $image->addResolutions($this->resolutions);
        $image->fill($imageData);
        $image->setParent($this->getParent());

        return $image;
    }
}