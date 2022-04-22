<?php
namespace Views\_Statistic\Models;
use Views\_Globals\Models\User;
use Views\_Main\Models\Main;
use Views\vendor\core\ActiveQuery;
use Views\vendor\libs\classes\Validator;

class Statistic extends Main 
{
	
	public $allModels;
	public $allComplects;

    /**
     * @return array
     * @throws \Exception
     */
    public function getUsersOnline() : array
    {
		$result = [];

		$aq = new ActiveQuery();
		$uo = $aq->registerTable('users_online');

		$result = $uo->select(['*'])->exe();
		$allUsers = $this->getUsers();

        foreach ( $result as &$user )
        {
            $userID = $user['user_id'];
            foreach ( $allUsers as $u )
            {
                if ( $userID == $u['id'] )
                    $user['fio'] = $u['fio'];
            }
        }

		return $result;
	}

    /**
     * Insert new user in table or update if already exist
     * @throws \Exception
     */
	public function setUserOnline()
    {
        //$sess_path = session_save_path();

        $aq = new ActiveQuery();
        $uo = $aq->registerTable('users_online');
        $rUo = $uo->select(['*'])->exe();

        $v = new Validator();

        $referer = $v->validateField('',$_SERVER['HTTP_REFERER'],['maxLength' => 254]);
        if ( strlen($referer) > 254 )
            $referer = substr($referer,0,253);

        $requestUri = $v->validateField('',$_SERVER['REQUEST_URI'],['maxLength' => 254]);
        if ( strlen($requestUri) > 254 )
            $requestUri = substr($requestUri,0,253);

        $device = $_SERVER['HTTP_SEC_CH_UA_PLATFORM']??'';

        foreach ( $rUo as $userO )
        {
            if ( $userO['session_id'] === session_id() )
            {
                // found this user. renew
                $row = [
                    'user_id' => User::getID(),
                    'referer'=>$referer,
                    'last_uri'=>$requestUri,
                    'device'=> $device,
                    'views_count'=>++$userO['views_count'],
                    'update_connect'=>date('Y-m-d H:i:s')
                ];
                $this->update('users_online', $row, ['session_id','=',session_id()] );
                return;
            }
        }

        // add new user in table
        $newUser = [
            'session_id' => session_id(),
            'user_id' => User::getID(),
            'user_ip' => $this->IP_visiter,
            'referer' => $referer,
            'last_uri' => $requestUri,
            'device' => $device,
            'views_count' => 1,
            'first_connect' => date('Y-m-d H:i:s'),
            'update_connect' => date('Y-m-d H:i:s'),
        ];
        $this->insertUpdateRows([$newUser],'users_online');
    }

    /**
     * Will remove users from online when their update_connect more then 1 hour pass
     * @throws \Exception
     */
    public function removeExpiredUsers()
    {
        $aq = new ActiveQuery();
        $uo = $aq->registerTable('users_online');
        $rUo = $uo->select(['*'])->exe();

        $currentTime = time();
        $toRemove = [];

        foreach ( $rUo as $userO )
        {
            $lastTime = strtotime($userO['update_connect'] . " +1 hour");
            if ( $lastTime < $currentTime )
            {
                $toRemove[] = $userO['id'];
            }
        }
        $this->removeRows($toRemove,'users_online');
    }

	/*
	public function getModels() {
		$result = array();
		$query_coll = mysqli_query($this->connection, " SELECT id,name FROM collections ORDER BY name");
		$i = 0;
		while( $collRow = mysqli_fetch_assoc($query_coll) )
        {
			$coll = $collRow['name'];
			
			$result[$i]['name'] = $collRow['name'];
			$result[$i]['id'] = $collRow['id'];
			
			$query_stock = mysqli_query($this->connection, " SELECT * FROM stock WHERE collections='$coll' ");
			
			while( $this->row[] = mysqli_fetch_assoc($query_stock) ){}
			array_pop($this->row);
			
			$result[$i]['wholePos'] = count($this->row);
			$compl = $this->countComplects();
			$result[$i]['wholeCompl'] = count($compl);
			
			$i++;
			$this->row = array();
		}
		return $result;
	}*/

	public function getLikedModels() {
		$result = array();
		$query_stock = mysqli_query($this->connection, " SELECT id,number_3d,vendor_code,model_type FROM stock ");

		while( $row = mysqli_fetch_assoc($query_stock) ){
			$id = $row['id'].';'.$row['number_3d'].' / '.$row['vendor_code'].' - '.$row['model_type'];
			$result['likes'][$id] = $row['likes'];
			$result['dislikes'][$id] = $row['dislikes'];
		}
		
		arsort($result['likes']);
		arsort($result['dislikes']);
		
		return $result;
	}
	public function getModelsBy3Dmodellers() {
		$result = array();
		
		$query_coll = mysqli_query($this->connection, " SELECT id,name FROM modeller3d ORDER BY name");
		$i = 0;
		while( $collRow = mysqli_fetch_assoc($query_coll) ){
			$coll = $collRow['name'];
			
			$result[$i]['name'] = $collRow['name'];
			
			$query_stock = mysqli_query($this->connection, " SELECT * FROM stock WHERE modeller3D='$coll' ");
			
			while( $this->row[] = mysqli_fetch_assoc($query_stock) ){}
			array_pop($this->row);
			
			$result[$i]['wholePos'] = count($this->row);
			$compl = $this->countComplects();
			$result[$i]['wholeCompl'] = count($compl);
			
			$i++;
			$this->row = array();
		}
		return $result;
	}
	public function getModelsByAuthors() {
		$result = array();
		
		$query_coll = mysqli_query($this->connection, " SELECT id,name FROM author ORDER BY name");
		$i = 0;
		while( $collRow = mysqli_fetch_assoc($query_coll) ){
			$coll = $collRow['name'];
			
			$result[$i]['name'] = $collRow['name'];
			
			$query_stock = mysqli_query($this->connection, " SELECT * FROM stock WHERE author='$coll' ");
			
			while( $this->row[] = mysqli_fetch_assoc($query_stock) ){}
			array_pop($this->row);
			
			$result[$i]['wholePos'] = count($this->row);
			$compl = $this->countComplects();
			$result[$i]['wholeCompl'] = count($compl);
			
			$i++;
			$this->row = array();
		}
		return $result;
	}
	/*
	public function scanBaseFileSizes() {
		$result = array();
		$result['imgFileSizes'] = 0;
		$result['imgFileCounts'] = 0;
		$result['stlFileSizes'] = 0;
		$result['stlFileCounts'] = 0;
		$result['overalCounts'] = 0;
		$result['overalSizes'] = 0;
		
		$query_img = mysqli_query($this->connection, " SELECT img_name,pos_id FROM images ");
		$query_stl = mysqli_query($this->connection, " SELECT stl_name,pos_id FROM stl_files ");
		
		while( $imgRow = mysqli_fetch_assoc($query_img) ) {
			
			$tempArr = explode('-',$imgRow['img_name']);
			$filename = $imgRow['img_name'];
			$n3d = $tempArr[0];
			$id = $imgRow['pos_id'];

			if ( file_exists($_SERVER['DOCUMENT_ROOT'].$this->stockDir.'/'.$n3d.'/'.$id.'/images/'.$filename) ) {
				$result['imgFileSizes'] += filesize($_SERVER['DOCUMENT_ROOT'].$this->stockDir.'/'.$n3d.'/'.$id.'/images/'.$filename);
				$result['imgFileCounts']++;
			}
		}
		$result['overalCounts'] += $result['imgFileCounts'];
		$result['overalSizes'] += $result['imgFileSizes'];
		$result['imgFileSizes'] = $this->human_filesize($result['imgFileSizes'], $decimals = 2);
		
		while( $stlRow = mysqli_fetch_assoc($query_stl) ) {
			
			$tempArr = explode('-',$stlRow['stl_name']);
			$filename = $stlRow['stl_name'];
			$n3d = $tempArr[0];
			$id = $stlRow['pos_id'];

			if ( file_exists($_SERVER['DOCUMENT_ROOT'].$this->stockDir.'/'.$n3d.'/'.$id.'/stl/'.$filename) ) {
				$result['stlFileSizes'] += filesize($_SERVER['DOCUMENT_ROOT'].$this->stockDir.'/'.$n3d.'/'.$id.'/stl/'.$filename);
				$result['stlFileCounts']++;
			}
		}
		$result['overalCounts'] += $result['stlFileCounts'];
		$result['overalSizes'] += $result['stlFileSizes'];
		$result['stlFileSizes'] = $this->human_filesize($result['stlFileSizes']);
		
		$result['overalSizes'] =  $this->human_filesize($result['overalSizes']);
		
		return $result;
	}
	*/
	
	public function human_filesize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
}