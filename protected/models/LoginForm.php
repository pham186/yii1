<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel {
 
     public $username;
     public $password;
     public $recovery;
     public $rememberMe;
     public $autoLogin = false;
     private $_identity;
 
     /**
      * Declares the validation rules.
      * The rules state that username and password are required,
      * and password needs to be authenticated.
      */
     public function rules() {
         return array(
             array('username, password', 'required', 'on' => 'login'),
             array('recovery', 'required', 'on' => 'recovery'),
             array('recovery', 'length', 'max' => 100),
             array('rememberMe', 'boolean'),
             array('password', 'authenticate'),
         );
     }
 
     /**
      * Declares attribute labels.
      */
     public function attributeLabels() {
         return array(
             'rememberMe' => Yii::t('default', 'Remember me next time'),
             'username' => Yii::t('default', 'Username'),
             'password' => Yii::t('default', 'Password'),
             'recovery' => Yii::t('default', 'Recovery'),
         );
     }
 
     /**
      * Authenticates the password.
      * This is the 'authenticate' validator as declared in rules().
      */
     public function authenticate($attribute, $params) {
         if (!$this->hasErrors()) {
             $this->_identity = new UserIdentity($this->username, $this->password, $this->autoLogin);
             if (!$this->_identity->authenticate())
                 $this->addError('password', Yii::t('default', 'Incorrect username or password'));
         }
     }
 
     /**
      * Logs in the user using the given username and password in the model.
      * @return boolean whether login is successful
      */
     public function login() {
         if ($this->_identity === null) {
             $this->_identity = new UserIdentity($this->username, $this->password, $this->autoLogin);
             $this->_identity->authenticate();
         }
 
         if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
             $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
             Yii::app()->user->login($this->_identity, $duration);
             User::model()->updateByPk($this->_identity->id, array('lastvisited_date' => new CDbExpression('NOW()')));
             return true;
         }
         else
             return false;
     }
 
 }