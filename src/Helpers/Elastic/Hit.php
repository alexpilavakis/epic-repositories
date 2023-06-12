<?php

namespace Ulex\EpicRepositories\Helpers\Elastic;

class Hit
{
    private string $_index;

    /** @var int|string */
    private $_id;
    private array $_source;

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->_index;
    }

    /**
     * @param string $index
     */
    public function setIndex(string $index): void
    {
        $this->_index = $index;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id): void
    {
        $this->_id = $id ?? '';
    }

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->_source;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->_source = $source;
    }
}
