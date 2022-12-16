<?php

namespace Album\Form;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Adapter\AdapterAwareInterface; 
use Laminas\Form\Element;

class GenreSelectElement extends Element\Select implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    public function init(): void
    {
        var_dump($this->adapter);

        if (! $this->adapter) {
            return;
        }

        /** @var Laminas\Db\Adapter\Driver\StatementInterface $statement */
        $statement = $this->adapter->query('SELECT `id`, `genre_name` FROM `genre`');
        $result    = $statement->execute();

  

        $options = [];
        /** @var array{id: int, name: string} $row */
        foreach ($result as $row) {
            $options[$row['id']] = $row['genre_name'];
        }

        $this->setValueOptions($options);
    }
}
