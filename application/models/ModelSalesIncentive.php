<?php

class ModelSalesIncentive extends CI_Model
{
    public function SI()
    {
        $query = $this->db->query("SELECT * FROM tb_sales_incentive");
        return $query->result_array();
    }

    public function SalesCode($salesCode, $fromDate, $endDate)
    {
        if (empty($salesCode)) {
            $query = $this->db->query("SELECT * FROM tb_sales_incentive WHERE BillingDate BETWEEN ? AND ?", array($fromDate, $endDate));
        } else {
            $query = $this->db->query("SELECT * FROM tb_sales_incentive WHERE SlsCd = ? AND BillingDate BETWEEN ? AND ?", array($salesCode, $fromDate, $endDate));
        }
        return $query->result_array();
    }

    public function StoreCode($storeCode, $fromDate, $endDate)
    {
        if (empty($storeCode)) {
            $query = $this->db->query("SELECT * FROM tb_sales_incentive WHERE BillingDate BETWEEN ? AND ?", array($fromDate, $endDate));
        } else {
            $query = $this->db->query("SELECT * FROM tb_sales_incentive WHERE Soffice = ? AND BillingDate BETWEEN ? AND ?", array($storeCode, $fromDate, $endDate));
        }
        return $query->result_array();
    }
}
