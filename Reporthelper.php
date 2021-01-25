<?php
/**
 * Created by PhpStorm.
 * User: kennethmaynard
 * Date: 10/01/20
 * Time: 12.53
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Reporthelper {
    private $CI;
    private $db;
    private $MD = array("MR", "SPO", "SMD");
    private $TL = array("KAE", "HIS", "tl");

    public function __construct() {
        $this->CI =& get_instance();
        $this->db = $this->CI->load->database();
    }

    public function get_visit($params, $extra_params = null){
        $year = $params['year'] ?? 0;
        $month = $params['month'] ?? 0;
        $start_date = $params['start_date'] ?? 0;
        $end_date = $params['end_date'] ?? 0;
        $start_datetime = $params['start_datetime'] ?? 0;
        $end_datetime = $params['end_datetime'] ?? 0;
        $_st_datetime = 0; $_en_datetime = 0;

        $region = $params['region'] ?? 0;
        $branch = $params['branch'] ?? 0;
        $city = $params['city'] ?? 0;

        $channel = $params['channel'] ?? 0;
        $cust_group = $params['cust_group'] ?? 0;
        $cust_group = $params['customer_group'] ?? $cust_group;

        $loc_3_level = $params['loc_3_level'] ?? 0;
        $loc_3_value = $params['loc_3_value'] ?? 0;


        if($loc_3_level and in_array($loc_3_level, $this->TL)){
            $_r = $this->CI->db->select("user_id as id")
                    ->where_in("parent_id", $loc_3_value)
                    ->get("user_login");
            $loc_3_value = array_reduce($_r->result(), function($accm, $v){
                                array_push($accm, $v->id);
                                return $accm;
            }, array());
        }


        if($year and $month){
            $_st_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $_en_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-31";
        }
        else if($start_date and $end_date){
            $_st_datetime = $start_date . " 00:00:00";
            $_en_datetime = $end_date . " 23:59:59";
        }
        else if($start_datetime and $end_datetime){
            $_st_datetime = $start_datetime;
            $_en_datetime = $end_datetime;
        }

        if($extra_params){
            if(isset($extra_params['select'])){
                foreach ($extra_params['select'] as $v){
                    $this->CI->db->select($v);
                }
            }

            if(isset($extra_params['where'])){
                foreach ($extra_params['where'] as $v){
                    $this->CI->db->where_in($v['index'], $v['value']);
                }
            }
        }

        $this->CI->db->select("v.visit_id, v.date start_datetime, v.end_datetime");

        $this->CI->db->from('visit as v')
                    ->join("store as s", 'v.store_id = s.store_id')
                    ->where("v.date >=", $_st_datetime)
                    ->where("v.date <=", $_en_datetime);

        $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
        $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id");

        if($loc_3_level){
            $this->CI->db->where_in("v.user_id", $loc_3_value);
        }

        if($city){
            $this->CI->db->where_in("s.city_id", $city);
        }
        else{
            //            if($branch or $region)  $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
            if($branch){
                $this->CI->db->where_in("lv2.branch_id", $branch);
            }
            if($region){
                $this->CI->db->where_in("lv2.region_id", $region);
            }
        }

        if($cust_group){
            $this->CI->db->where_in('s.customer_group_id', $cust_group);
        }
        else{
            if($channel){
                //                $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id")
                //                        ->where_in("lv1.channel_id", $channel);
                $this->CI->db->where_in("lv1.channel_id", $channel);
            }
        }



        return $this->CI->db->get_compiled_select();
    }




    public function get_jp($params, $extra_params = null){
        $year = $params['year'] ?? 0;
        $month = $params['month'] ?? 0;
        $start_date = $params['start_date'] ?? 0;
        $end_date = $params['end_date'] ?? 0;
        $start_datetime = $params['start_datetime'] ?? 0;
        $end_datetime = $params['end_datetime'] ?? 0;
        $_st_datetime = 0; $_en_datetime = 0;

        $region = $params['region'] ?? 0;
        $branch = $params['branch'] ?? 0;
        $city = $params['city'] ?? 0;

        $channel = $params['channel'] ?? 0;
        $cust_group = $params['cust_group'] ?? 0;


        if($year and $month){
            $_st_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
            $_en_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-31 23:59:59";
        }
        else if($start_date and $end_date){
            $_st_datetime = $start_date . " 00:00:00";
            $_en_datetime = $end_date . " 23:59:59";
        }
        else if($start_datetime and $end_datetime){
            $_st_datetime = $start_datetime;
            $_en_datetime = $end_datetime;
        }

        if($extra_params){
            if(isset($extra_params['select'])){
                foreach ($extra_params['select'] as $v){
                    $this->CI->db->select($v);
                }
            }
        }

        $this->CI->db->select("");

        $this->CI->db->from('journey_plan as v')
            ->join("store as s", 'v.store_id = s.store_id')
            ->where("v.call_date >=", $_st_datetime)
            ->where("v.call_date <=", $_en_datetime);

        $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
        $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id");

        if($city){
            $this->CI->db->where_in("s.city_id", $city);
        }
        else{
            //            if($branch or $region)  $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
            if($branch){
                $this->CI->db->where_in("lv2.branch_id", $branch);
            }
            if($region){
                $this->CI->db->where_in("lv2.region_id", $region);
            }
        }

        if($cust_group){
            $this->CI->db->where_in('s.customer_group_id', $cust_group);
        }
        else{
            if($channel){
                //                $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id")
                //                        ->where_in("lv1.channel_id", $channel);
                $this->CI->db->where_in("lv1.channel_id", $channel);
            }
        }
        return $this->CI->db->get_compiled_select();
    }

    public function get_coverage_perfect_store($params, $extra_params = null){
        $year = $params['year'] ?? 0;
        $month = $params['month'] ?? 0;
        $start_date = $params['start_date'] ?? 0;
        $end_date = $params['end_date'] ?? 0;
        $start_datetime = $params['start_datetime'] ?? 0;
        $end_datetime = $params['end_datetime'] ?? 0;
        $_st_datetime = 0; $_en_datetime = 0;

        $region = $params['region'] ?? 0;
        $branch = $params['branch'] ?? 0;
        $city = $params['city'] ?? 0;

        $channel = $params['channel'] ?? 0;
        $cust_group = $params['cust_group'] ?? 0;
        $cust_group = $params['customer_group'] ?? $cust_group;


        if($year and $month){
            $_st_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
            $_en_datetime = $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-31 23:59:59";
        }
        else if($start_date and $end_date){
            $_st_datetime = $start_date . " 00:00:00";
            $_en_datetime = $end_date . " 23:59:59";
        }
        else if($start_datetime and $end_datetime){
            $_st_datetime = $start_datetime;
            $_en_datetime = $end_datetime;
        }

        if($extra_params){
            if(isset($extra_params['select'])){
                foreach ($extra_params['select'] as $v){
                    $this->CI->db->select($v);
                }
            }
        }

        $this->CI->db->select("v.store_id");

        $this->CI->db->from('visit as v')
            ->join("batch_perfect_store bps","v.visit_id = bps.visit_id")
            ->join("store as s", 'v.store_id = s.store_id')
            ->where("v.start_datetime >=", $_st_datetime)
            ->where("v.start_datetime <=", $_en_datetime);

        $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
        $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id");


        if($city){
            $this->CI->db->where_in("s.city_id", $city);
        }
        else{
//            if($branch or $region)  $this->CI->db->join("loc_view as lv2", "s.city_id = lv2.city_id");
            if($branch){
                $this->CI->db->where_in("lv2.branch_id", $branch);
            }
            if($region){
                $this->CI->db->where_in("lv2.region_id", $region);
            }
        }

        if($cust_group){
            $this->CI->db->where_in('s.customer_group_id', $cust_group);
        }
        else{
            if($channel){
//                $this->CI->db->join("cust_group_view as lv1", "s.customer_group_id= lv1.customer_group_id")
//                        ->where_in("lv1.channel_id", $channel);
                $this->CI->db->where_in("lv1.channel_id", $channel);
            }
        }
//        $this->CI->db->group_by("store_id");
        return $this->CI->db->get_compiled_select();
    }
}
