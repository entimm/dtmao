<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-26 14:37:02
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-26 16:38:38
 */

namespace Home\Model;

class ToolsModel {
    public function getAddrInfo($data) {
        preg_match_all('/(.*?)\,(.*?);/', $data, $matches);
        //var_dump($matches);exit;
        return $matches;
    }
}
