<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Entity;

interface CartEntityInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getObjectTypeKey();

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Lazy-loading getter
     *  ideal for usage in the View layer
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param array|string|int $param1
     * @param mixed|null $param2
     * @return $this
     */
    public function setData($param1, $param2 = null);

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data);

    /**
     * @return array
     */
    public function getBaseData();

    /**
     * Getter , after fully loading
     *  use only if necessary, and avoid calling multiple times
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '');
}
