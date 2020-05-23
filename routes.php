<?php

global $routes;

$routes['/users/login'] = '/users/login';
$routes['/users/new'] = '/users/new_record';
$routes['/users/feed'] = '/users/feed/';
$routes['/users/{id}'] = '/users/view/:id';
$routes['/users/{id}/photos'] = '/users/photos/:id';
$routes['/users/{id}/follow'] = '/users/follow/:id';



/*
users/login				POST		logar no sistema
users/new				POST		adicionar usuário	
users/{id}				GET			pegar informações do usuário{id}	
users/{id}				PUT			editar usuário 		
users/{id} 				DELETE		excluir usuário 	
users/{id}/feed			GET			feed de fotos do usuário{id}
users/{id}/photos		GET			fotos do usuários	
users/{id}/follow		POST		seguir usuário   
users/{id}/unfollow		DELETE		deseguir usuário  

photos/random			GET			fotos aleatórias
photos/new				POST		nova foto 	
photos/{id} 			GET			informações da foto 
photos/{id} 			DELETE		excluir a foto   	
photos/{id}/comment 	POST		inserir comentário na foto{id}
photos/{id}/comment 	DELETE		apagar comentário na foto{id} 	
photos/{id}/like 		POST		curtir a foto{id}
photos/{id}/unlike 		DELETE		descurtir a foto{id}
*/
