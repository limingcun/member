<?php
namespace App\Http\Repositories;
use Illuminate\Http\Request;

abstract class BaseRepository {

    protected $table;
    /**
     * Get number of records.
     *
     * @return array
     */
    public function getNumber()
    {
        $total = $this->table->count();
        $new = $this->table->whereSeen(0)->count();
        return compact('total', 'new');
    }
    /**
     * Destroy a model.
     *
     * @param  int $id
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->getById($id)->delete();
    }
    /**
     * Get Model by id.
     *
     * @param  int  $id
     * @return App\Models\Model
     */
    public function getById($id)
    {
        return $this->table->findOrFail($id);
    }

    public function getAll($colums = ['*'])
    {
        return $this->table->select($colums)->get();
    }

    public function filterSpace(Array $data = [])
    {
        $data = filter_value($data);
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
    
    /*
     * 条件查询公共方法
     * $arrs(前端传入值=>索引值)
     */
    public function whereQuery(&$model, $arrs = [], Request $request) {
        foreach($arrs as $k => $a) {
            if ($request->$k != '') {
                $q = $this->whereField($k);
                $is_date = $this->isDate($request->$k);
                if (!$is_date) {
                    $model = $model->where($a, $q, $request->$k);
                } else {
                    $model = $model->whereDate($a, $q, $request->$k);
                }
            }
        }
        return $model;
    }
    
    /*
     * 获取查询条件(>=, <=, =)
     */
    public function whereField($str) {
        if(strpos($str, '_min') !== false || strpos($str, '_start') !== false){ 
            return '>=';
        } else if (strpos($str, '_max') !== false || strpos($str, '_end') !== false) {
            return '<=';
        } else {
            return '=';
        }
    }
    
    /**
     * 自定义分页方法
     * @param type $page
     * @param type $obs
     * @param type $size
     * @return type
     */
    public function pageSize($page, $obs, $size) {
        $arr = [];
        $count = count($obs);
        if($count > $page + $size) {
            $page_size = $page + $size;
        } else {
            $page_size = $count;
        }
        for($i = $page; $i<$page_size; $i++) {
            $arr[] = $obs[$i];
        }
        return $arr;
    }
    
    /**
     * 判断是否是日期格式
     * $date日期格式
     */
    public function isDate($date) {
        if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)){
            if(checkdate($parts[2],$parts[3],$parts[1]))
                return true;
            else
            return false;
        }
        else
            return false;
    }
    
    /**
     * 分页器属性设置
     * $data数据
     * $page分页
     * $page_size当前页码
     * $total全部数量
     */
    public function burster($data, $page, $page_size, $total) {
        $result['data'] = $data;
        $pagination['current_page'] = $page;
        $pagination['per_page'] = $page_size;
        $pagination['total'] = $total;
        $result['pagination'] = $pagination;
        return $result;
    }
}
