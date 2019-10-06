##### Simple entity loggable bundle

###### How to use:

1. Install bundle
`compoer require ruspanzer/loggable-bundle`
2. Implement `Ruspanzer\LoggableBundle\Entity\Interfaces\LoggableInterface` for you entity
3. If you need set relations between two loggable entities, use `getRelatedLogEntities` in related entity. 
This method must be return array of `LoggableInterface` entities. 
It will allow search related logs when searching main entity
4. Find logs with repository method getByObject(). Or you can write your search implementation with pagination and other cool features :-)

###### Example:

```
class Place implements LoggableInterface
{
    /**
    * @ORM\Id()
    */
    private $id;

    /**
    * @ORM\OneToOne(targetEntity="Address")
    */
    private $address;
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getRelatedLogEntities() 
    {
        return [];
    }
}

class Address implements LoggableInterface
{
    /**
    * @ORM\Id()
    */
    private $id;

    /**
    * @ORM\OneToOne(targetEntity="Place")
    * @ORM\JoinColumn(name="place_id")
    */
    private $place;
    
    public function getId() 
    {
        return $this->id;
    }

    public function getPlace() 
    {
        return $this->place;
    }
    
    public function getRelatedLogEntities() 
    {
        return [
            $this->getPlace();
        ];
    }
}
```

If you will be search logs by Place, Address logs to be returned