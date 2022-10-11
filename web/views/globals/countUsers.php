<?php
	
	$mysqli = new mysqli("localhost", "adm_test", "V7L0QJk3YOHvMqnC", "huf_models");
	$ip = $_SERVER['HTTP_X_REAL_IP'];
	//$ip = getUserHostAddress();

	if ( $mysqli->connect_errno ) {
		//printf("Can't connect to db: %s\n", $mysqli->connect_error);
		//exit();
	}
	/* изменение набора символов на utf8 */
	if (!$mysqli->set_charset("utf8")) {
		//printf("Error to loading symbols utf8: %s\n", $mysqli->error);
		//exit();
	}
	$id_session = session_id();
	$userFio = $_SESSION['user']['fio'];
	
	  // Проверяем, присутствует ли такой id в базе данных 
	  $ses = $mysqli->query("SELECT * FROM sessions WHERE id_session = '$id_session'"); 
	  //if(!$ses) exit("<p>Ошибка в запросе к таблице сессий</p>"); 
	  
	  // Если сессия с таким номером уже существует, 
	  // значит пользователь online - обновляем время его 
	  // последнего посещения 
	  
	  if( $ses->num_rows > 0 ) {
		$mysqli->query("UPDATE sessions SET putdate=NOW(),ip='$ip', user='$userFio' WHERE id_session = '$id_session'"); 
	  } else {
		// Иначе, если такого номера нет - посетитель только что 
		// вошёл - помещаем в таблицу нового посетителя 
		$query = $mysqli->query(" INSERT INTO sessions (id_session,putdate,ip,user) VALUES('$id_session',NOW(),'$ip','$userFio') ");
		if( !$query ) { 
		 // printf( "Error_add user: %s\n", $mysqli->error );
		 // echo "<p>Ошибка при добавлении пользователя</p>"; 
		  //exit(); 
		} 
	  }
	  
	  // Будем считать, что пользователи, которые отсутствовали 
	  // в течении 20 минут - покинули ресурс - удаляем их 
	  // id_session из базы данных
	$mysqli->query(" DELETE FROM sessions WHERE putdate < NOW() -  INTERVAL '15' MINUTE ");
	$mysqli->close();
?>