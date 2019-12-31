<?php

namespace Main\Models;

use Main\Domain\Customer;
use Main\Exceptions\DbException;
use Main\Exceptions\NotFoundException;
use PDO;

class CustomerModel extends AbstractModel {
    const CLASSNAME = '\Main\Domain\Customer';

    public function getAll($page, $pageLength) {
        $start = $pageLength * ($page - 1);

        $query = 'SELECT * FROM customers LIMIT :page, :length';
        $sth = $this->db->prepare($query);
        $sth->bindParam('page', $start, PDO::PARAM_INT);
        $sth->bindParam('length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
    
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

}
