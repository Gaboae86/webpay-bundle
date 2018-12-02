<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 20-11-18
 * Time: 9:25
 */

namespace GabrielCorrea\WebpayBundle\Model;


use GabrielCorrea\WebpayInterface\GabrielCorrea\WebpayInterface\src\Interfaces\Entity\TransactionResultInterface;

class TransactionRecord implements TransactionResultInterface
{
    /**
     * @var string
     */
    private $buyOrder;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @return string
     */
    public function getBuyOrder(): string
    {
        return $this->buyOrder;
    }

    /**
     * @param string $buyOrder
     */
    public function setBuyOrder(string $buyOrder): void
    {
        $this->buyOrder = $buyOrder;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}