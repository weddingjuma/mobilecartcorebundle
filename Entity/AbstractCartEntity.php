<?php

namespace MobileCart\CoreBundle\Entity;

abstract class AbstractCartEntity
{
    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $vars = get_object_vars($this);
        if (array_key_exists($key, $vars)) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Lazy-loading getter
     *  ideal for usage in the View layer
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        $data = $this->getBaseData();
        if (isset($data[$key])) {
            return $data[$key];
        }

        $data = $this->getData();
        if (isset($data[$key])) {

            if (is_array($data[$key])) {
                return implode(',', $data[$key]);
            }

            return $data[$key];
        }

        return '';
    }

    /**
     * @param $param1
     * @param null $param2
     * @return $this
     */
    public function setData($param1, $param2 = null)
    {
        if (is_array($param1)) {
            return $this->fromArray($param1);
        } elseif (is_scalar($param1)) {
            return $this->set($param1, $param2);
        }

        return $this;
    }

    /**
     * Get All Data or specific key of data
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        $data = $this->getBaseData();
        if (strlen($key) > 0) {
            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        if (!$data) {
            return $this;
        }

        foreach($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // todo : loop on data and adjust \DateTime object values
        // todo : rename to getLuceneData() and re-implement
        return $this->getBaseData();
    }

    abstract function getBaseData();
}
