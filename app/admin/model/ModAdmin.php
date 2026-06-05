<?php
namespace App\admin\model;

use YXLib\foundation\Model;
use YXLib\support\Util;
use App\admin\service\SrvAuth;
use App\common\Table;

class ModAdmin extends Model{

    protected $conn = 'admin';

    public function getAdminList($data)
    {
        $sql = "select * from `".Table::$admin_user."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$admin_user."` where 1 ";
        if($data['nick']){
            $param['nick'] = '%'.$data['nick'].'%';
            $sql .= "and nick like :nick ";
            $sql_c .= "and nick like :nick ";
        }
        if($data['user']){
            $param['user'] = $data['user'];
            $sql .= "and user = :user ";
            $sql_c .= "and user = :user ";
        }
        if($data['id']){
            $param['id'] = $data['id'];
            $sql .= "and id = :id ";
            $sql_c .= "and id = :id ";
        }
        if(isset($data['state'])){
            $param['state'] = $data['state'];
            $sql .= "and state = :state ";
            $sql_c .= "and state = :state ";
        }
        if($data['data_id']){
            $param['data_id'] = $data['data_id'];
            $sql .= "and data_id = :data_id ";
            $sql_c .= "and data_id = :data_id ";
        }
        if($data['mk_id']){
            $param['mk_id'] = $data['mk_id'];
            $sql .= "and mk_id = :mk_id ";
            $sql_c .= "and mk_id = :mk_id ";
        }
        if($data['role_id']){
            $param['role_id'] = $data['role_id'];
            $sql .= "and role_id = :role_id ";
            $sql_c .= "and role_id = :role_id ";
        }
        if($data['page']){
            $limit = $this->getLimit($data['page'],15);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => (int)$count['c'],
        );
    }

    /**
     * 获取管理员信息
     * @param $admin_id
     * @return array|bool|resource|string
     */
    public function getAdminInfo($admin_id){
        $sql = "select * from `".Table::$admin_user."` where `admin_id`=:admin_id";
        $info = $this->getOne($sql,array('admin_id' => $admin_id));

        return $info;
    }

    /**
     * 删除管理员
     * @param $admin_id
     * @return int
     */
    public function deleteAdminAction($admin_id){
        $this->delete(array('admin_id'=>array('=',$admin_id)),1,Table::$admin_user);
        return $this->affectedRows();
    }

    /**
     * 更新用户状态
     *
     * @param [type] $adminId
     * @param [type] $status
     * @return void
     */
    public function updateAdminStatusAction($adminId,$status){
        $this->update(array('state'=>$status-1),array('admin_id'=>$adminId),Table::$admin_user);
        return $this->affectedRows();
    }

    /**
     * 更新管理后台用户
     * @param $admin_id
     * @param $data
     * @return int
     */
    public function updateAdminAction($admin_id,$data){
        $where = array(
            'admin_id' => $admin_id,
        );
        $this->update($data,$where,Table::$admin_user);
        return $this->affectedRows();

    }
    public function updatePwAction($admin_id,$data){
        $update = array();
        if($data['pwd']){
            $update['salt'] = Util::getSalt(10);
            $update['pwd'] = SrvAuth::signPwd($data['user'],$data['pwd'],$update['salt']);
        }
        $where = array(
            'admin_id' => $admin_id,
        );

        $this->update($update,$where,Table::$admin_user);
        return $this->affectedRows();
    }

    /**
     * 添加
     * @param $data
     * @param array $extra_info
     * @return resource|string
     */
    public function addAdminAction($data){
        $admin_id = $this->insert($data,true,Table::$admin_user);
        return $admin_id;
    }


    //角色
    public function getRoleList($data)
    {
        $sql = "select * from `".Table::$roles."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$roles."` where 1 ";
        if($data['name']){
            $param['name'] = '%'.$data['name'].'%';
            $sql .= "and name like :cname ";
            $sql_c .= "and name like :cname ";
        }
        if($data['id']){
            $param['id'] = $data['id'];
            $sql .= "and id = :id ";
            $sql_c .= "and id = :id ";
        }
        if($data['page']){
            $limit = $this->getLimit($data['page'],15);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getRoleInfo($id)
    {
        $sql = "select * from `".Table::$roles."` where id =:id";
        return $this->getOne($sql,['id'=>$id]);
    }

    public function deleteRoleAction($id)
    {
        if(!$id) return false;
        $this->delete(array('id'=>$id),1,Table::$roles);
        return $this->affectedRows();
    }

    public function addRoleAction($data)
    {
        $insert = [
            'display_name'=>$data['display_name'],
            'atime'=>time()
        ];
        return $this->insert($insert ,false,Table::$roles);
    }

    public function updateRoleAction($id,$data)
    {
        if(!$id) return false;
        $this->update($data ,['id'=>$id],Table::$roles);
        return $this->affectedRows();
    }

    //-------------------------------------

    public function getDataAuthorityList($data)
    {
        $sql = "select id,name,mtime,atime from `".Table::$admin_data_authority."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$admin_data_authority."` where 1 ";
        if($data['name']){
            $param['name'] = '%'.$data['name'].'%';
            $sql .= "and name like :cname ";
            $sql_c .= "and name like :cname ";
        }
        if($data['id']){
            $param['id'] = $data['id'];
            $sql .= "and id = :id ";
            $sql_c .= "and id = :id ";
        }
        if($data['page']){
            $limit = $this->getLimit($data['page'],15);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getDataAuthorityInfo($id)
    {
        $sql = "select * from `".Table::$admin_data_authority."` where id =:id";
        return $this->getOne($sql,['id'=>$id]);
    }

    public function deleteDataAuthorityAction($id)
    {
        if(!$id) return false;
        $this->delete(array('id'=>$id),1,Table::$admin_data_authority);
        return $this->affectedRows();
    }

    public function addDataAuthorityAction($data)
    {
        $insert = [
            'name'=>$data['name'],
            'atime'=>time()
        ];
        return $this->insert($insert ,false,Table::$admin_data_authority);
    }

    public function updateDataAuthorityAction($id,$data)
    {
        if(!$id) return false;
        $this->update($data ,['id'=>$id],Table::$admin_data_authority);
        return $this->affectedRows();
    }

    public function getDataAuthroityMGame()
    {
        $sql = "select mgid as value,mgname as title from ".Table::$pf_game_main." where status = 0";
        $result = [];
        $list = $this->query($sql);
        foreach($list as $v){
            $result[] = [
                'value'=>$v['value'],
                'title'=>$v['value'].'-'.$v['title'],
            ];
        }
        return $result;
    }

    public function getDataAuthroityPids()
    {
        $sql = "select pid as value,pname as title from ".Table::$pf_partner." where 1";
        $result = [];
        $list = $this->query($sql);
        foreach($list as $v){
            $result[] = [
                'value'=>$v['value'],
                'title'=>$v['value'].'-'.$v['title'],
            ];
        }
        return $result;
    }

    public function getDataAuthroityCids()
    {
        $sql = "select cid as value,cname as title from ".Table::$pf_channel." where 1";
        $result = [];
        $list = $this->query($sql);
        foreach($list as $v){
            $result[] = [
                'value'=>$v['value'],
                'title'=>$v['value'].'-'.$v['title'],
            ];
        }
        return $result;
    }

    //-------------------------------------
    public function getMkAuthorityList($data)
    {
        $sql = "select id,name,mtime,atime from `".Table::$admin_mk_authority."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$admin_mk_authority."` where 1 ";
        if($data['name']){
            $param['name'] = '%'.$data['name'].'%';
            $sql .= "and name like :cname ";
            $sql_c .= "and name like :cname ";
        }
        if($data['id']){
            $param['id'] = $data['id'];
            $sql .= "and id = :id ";
            $sql_c .= "and id = :id ";
        }
        if($data['page']){
            $limit = $this->getLimit($data['page'],15);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getMkAuthorityInfo($id)
    {
        $sql = "select * from `".Table::$admin_mk_authority."` where id =:id";
        return $this->getOne($sql,['id'=>$id]);
    }

    public function deleteMkAuthorityAction($id)
    {
        if(!$id) return false;
        $this->delete(array('id'=>$id),1,Table::$admin_mk_authority);
        return $this->affectedRows();
    }

    public function addMkAuthorityAction($data)
    {
        $insert = [
            'name'=>$data['name'],
            'atime'=>time()
        ];
        return $this->insert($insert ,false,Table::$admin_mk_authority);
    }

    public function updateMkAuthorityAction($id,$data)
    {
        if(!$id) return false;
        $this->update($data ,['id'=>$id],Table::$admin_mk_authority);
        return $this->affectedRows();
    }

    public function getPitcherList()
    {
        $sql = "SELECT a.`pitid`,a.`pitname`,b.`teamid`,b.`teamname` FROM `".Table::$mk_pitcher."` a left join ".Table::$mk_pitcher_team." b on a.teamid=b.teamid";
        return $this->query($sql);
    }

    //-----------------------------

    public function getMenuList($params = [])
    {
        $sql = "select * from `".Table::$permissions."` where 1";
        if($params['ids']){
            $sql .= " and id in (:ids)";
        }
        if($params['show_menu']){
            $sql .= " and show_menu = 1";
        }
        if($params['type']){
            $sql .= " and type = 1";
        }
        $sql .= " order by `sort` desc";
        return $this->query($sql,$params);
    }

    public function getMenuInfo($id)
    {
        $sql = "select * from `".Table::$permissions."` where id = :id";
        return $this->getOne($sql,['id'=>$id]);
    }

    public function addMenuAction($data)
    {
        return $this->insert($data ,false,Table::$permissions);
    }

    public function updateMenuAction($id,$data)
    {
        if(!$id) return false;
        $this->update($data ,['id'=>$id],Table::$permissions);
        return $this->affectedRows();
    }
    public function deleteMenuAction($id)
    {
        if(!$id) return false;
        $this->delete(array('id'=>$id),1,Table::$permissions);
        return $this->affectedRows();
    }

    public function roleHasPermission($roleId)
    {
        if(!$roleId) return [];
        $sql = "select permission_id from ".Table::$role_has_permissions." where role_id=:role_id";
        $ret = $this->query($sql,['role_id'=>$roleId]);

        $permission = array_column($ret,'permission_id');
        sort($permission);
        return $permission;
    }

    public function addRolePermission($roleId,$permissionIds)
    {
        $sql = "delete from ".Table::$role_has_permissions." where role_id=:role_id";
        $this->query($sql,['role_id'=>$roleId]);
        
        $insert = [];
        foreach($permissionIds as $id){
            $insert[] = "($roleId,$id)";
        }

        $sql = "insert into ".Table::$role_has_permissions." (`role_id`,`permission_id`) values ".implode(',',$insert);
        $this->query($sql);

        return true;
    }

}