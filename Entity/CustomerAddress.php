<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerAddress
 *
 * @ORM\Table(name="customer_address")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CustomerAddressRepository")
 */
class CustomerAddress
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \MobileCart\CoreBundle\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer", inversedBy="addresses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=24, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=255, nullable=true)
     */
    protected $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255)
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=16, nullable=true)
     */
    protected $postcode;

    /**
     * @var string
     *
     * @ORM\Column(name="country_id", type="string", length=2, nullable=true)
     */
    protected $country_id;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CUSTOMER_ADDRESS;
    }

    /**
     * Getter , after fully loading
     *  use only if necessary, and avoid calling multiple times
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        $data = $this->getBaseData();

        $data['customer_id'] = $this->getCustomer()
            ? $this->getCustomer()->getId()
            : null;

        if (strlen($key) > 0) {

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id'         => $this->getId(),
            'firstname'  => $this->getFirstname(),
            'lastname'   => $this->getLastname(),
            'company'    => $this->getCompany(),
            'phone'      => $this->getPhone(),
            'street'     => $this->getStreet(),
            'street2'    => $this->getStreet2(),
            'city'       => $this->getCity(),
            'region'     => $this->getRegion(),
            'postcode'   => $this->getPostcode(),
            'country_id' => $this->getCountryId(),
        ];
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return CustomerAddress
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return CustomerAddress
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return "{$this->getFirstname()} {$this->getLastname()}";
    }

    /**
     * @param $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return CustomerAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return CustomerAddress
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street
     *
     * @param string $street2
     * @return CustomerAddress
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
        return $this;
    }

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return CustomerAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return CustomerAddress
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return CustomerAddress
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string 
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set country_id
     *
     * @param string $countryId
     * @return CustomerAddress
     */
    public function setCountryId($countryId)
    {
        $this->country_id = $countryId;
        return $this;
    }

    /**
     * Get country_id
     *
     * @return string 
     */
    public function getCountryId()
    {
        return $this->country_id;
    }
}
