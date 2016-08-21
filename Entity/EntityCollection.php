<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class EntityCollection.
 *
 * @author Christophe Pyree <chpyr@smile.fr>
 */
class EntityCollection extends ArrayCollection
{
    /**
     * @param string       $fieldName
     * @param string|array $fieldValues
     * @param bool         $revert
     *
     * @return EntityCollection
     */
    final public function filterBy($fieldName, $fieldValues, $revert = false)
    {
        $results = [];
        $fieldValues = (array)$fieldValues;
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($this->getValues() as $object) {
            foreach ($fieldValues as $fieldValue) {
                if ($revert === false && $propertyAccess->getValue($object, $fieldName) === $fieldValue) {
                    $results[] = $object;
                    break;
                }
                //This part play like a "not" sql condition on collection
                if ($revert === true && $propertyAccess->getValue($object, $fieldName) !== $fieldValue) {
                    $results[] = $object;
                    break;
                }
            }
        }

        return new static($results);
    }

    /**
     * @param string $fieldName
     *
     * @return float
     */
    final public function sumBy($fieldName)
    {
        $result = 0;
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($this->getValues() as $object) {
            $result += $propertyAccess->getValue($object, $fieldName);
        }

        return $result;
    }
}