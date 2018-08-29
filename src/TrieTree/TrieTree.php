<?php

namespace TrieTree;

class TrieTree {
    protected $nodeTree = [];
    protected $mix = [];
    public static $instance = null;
    /**
     * 构造
     * TrieTree constructor.
     */
    public function __construct() {
       
    }
    
    public static function _getInstance(){
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function initTree($words,$mix){
    	$this->nodeTree = [];
        if (is_array($words) && count($words) == count($words,1)) {
	    foreach($words as $one){
                $this->append($one);
            }

	}
        if(is_array($mix) && count($mix) == count($mix,1)){
            $this->mix = $mix;  
	}
    }

    /**
     * 添加词到敏感词树中(utf-8)
     * @param $index_str 添加的词
     * @return $this
     */
    public function append($index_str) {
        $str = trim($index_str);
        
        $childTree = &$this->nodeTree;
        $len = mb_strlen($str,'utf-8');
        for ($i = 0; $i < $len;$i++) {
            $word = mb_substr($index_str,$i,1,'utf-8');
            $code = bin2hex($word);
            $is_end = false;
            if ($i == ($len - 1)) {
                $is_end = true;
            }
            $childTree = &$this->_appendWordToTree($childTree, $code, $word, $is_end,$str);
        }
        
        unset($childTree);
        return $this;
    }
    
    /**
     * 追加一个字[中英文]到树中
     * @param $tree
     * @param $code
     * @param $word
     * @param bool $end
     * @param array $data
     * @param string $full_str
     * @return mixed
     */
    private function &_appendWordToTree(&$tree, $code, $word, $end = false, $full_str = '') {
        if (!isset($tree[$code])) {
            $tree[$code] = array(
                'end' => $end,
                'child' => array(),
                'value' => $word,
            );
        }
        if ($end) {
            $tree[$code]['end'] = true;
            $tree[$code]['full'] = $full_str;
        }
        return $tree[$code]['child'];
    }
    
    /**
     * 获得整棵树
     * @return array
     */
    public function getTree() {
        return $this->nodeTree;
    }
    
    /**
     * overwrite tostring.
     * @return string
     */
    public function __toString() {
        // TODO: Implement __toString() method.
        return json_encode($this->nodeTree);
    }
    
    /**
     * 查找敏感词
     * @param string
     * @return array
     */
    public function search($search,$replace = '') {
        $search = trim($search);
        if (empty($search)) {
            return false;
        }

        //命中集合
        $hit_arr = array();
        $tree = &$this->nodeTree;
        $arr_len = mb_strlen($search,'utf-8');
        for ($i = 0; $i < $arr_len; $i++) {
            $wordLength = 0;
            //若命中了一个索引 则继续向下寻找
            $tree = &$this->nodeTree;
            for ($j = $i;$j < $arr_len;$j++) {
                $word = mb_substr($search, $j, 1,'utf-8');
                $index = bin2hex($word);
                if (in_array($word,$this->mix)) {
                    $wordLength++;
                    continue;
                }
                if (!isset($tree[$index])) {
                    break;
                }
                $wordLength++;
                $node = $tree[$index];
                
                if ($node['end']) {
                    $hit_word = mb_substr($search,$i,$wordLength);
                    if (!in_array($hit_word,$hit_arr)) {
                        $hit_arr[] = $hit_word;
                    }
                    $tree = &$tree[$index]['child'];
                } else if (!empty($node['child'])) {
                    $tree = &$tree[$index]['child'];
                }
            }
            if ($wordLength > 0) {
                $i += ($wordLength-1);
            }
        }
        $result = $search;
        if (!empty($replace)) {
            $result = str_replace($hit_arr,$replace,$search);
        }
        unset($tree, $search_keys);
        return ['search'=>$search,'hit_arr'=>$hit_arr,'result'=>$result];
    }
    
    /**
     * 将字符转为16进制标示
     * @param $str
     * @return array
     */
//    public function convertStrToH($str) {
//        $len = mb_strlen($str);
//        $chars = [];
//        for ($i = 0; $i < $len;$i++) {
//            $chars[] = bin2hex(mb_substr($str,$i,1));
//        }
//        return $chars;
//    }
}
