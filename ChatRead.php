<?php

namespace frontend\modules\dashboard\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\db\Command;


/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Auth[] $auths
 * @property Profile $profile
 */
class ChatRead extends ActiveRecord { 
    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chat_message_read';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chat_message_id', 'chat_id', 'user_id'], 'required'],
            [['id', 'chat_message_id', 'chat_id', 'user_id', 'message_read'], 'integer']
        ];
    }
    
    
    public function CountChatContest($user_id) {
        
        if(!empty($user_id))
        {
            $user_id = (int) $user_id; 
            $CountsChat = Yii::$app->db->createCommand('SELECT COUNT(chat_message_read.id) as countChat
                                                        FROM chat_message_read 
                                                        LEFT JOIN chat_message_user ON chat_message_user.message_id = chat_message_read.chat_message_id 
                                                        LEFT JOIN chat_message ON chat_message.id = chat_message_user.message_id
                                                        LEFT JOIN chat ON chat.id = chat_message.chat_id  
                                                        WHERE chat_message_user.user_id = '.$user_id.'
                                                        AND chat.sub_cat_id = 2
                                                        AND
                                                        chat_message.status_id = 1
                                                        AND
                                                        chat_message_user.unread = 1
                                                        AND
                                                        chat_message.moderation = 1
                                                        AND chat_message_read.message_read = 0 
                                                        ORDER BY chat_message_user.`id` DESC')->queryAll();
             
        }
        return !empty($CountsChat[0]['countChat']) ? $CountsChat[0]['countChat'] : 0;
                                                                      
    }
    
    public function ChatContestList($user_id) { 
        
        if(!empty($user_id))
        {
            $user_id = (int) $user_id;
            $CountsChat = Yii::$app->db->createCommand('SELECT 
                                                        DISTINCT(chat.id) AS chat_id,
                                                        order_data.order_id AS order_id,
                                                        order_data.title as title, 
                                                        chat_message.date_create as date
                                                        FROM `chat` 
                                                        LEFT JOIN chat_message_read ON chat_message_read.chat_id = chat.id 
                                                        LEFT JOIN chat_message ON chat_message.id = chat_message_read.chat_message_id
                                                        LEFT JOIN chat_message_user ON chat_message_user.message_id = chat_message_read.chat_message_id
                                                        LEFT JOIN order_data ON order_data.order_id = chat.creator_id
                                                        WHERE 
                                                        chat_message_user.user_id = '.$user_id.' 
                                                        AND 
                                                        chat_message_read.message_read = 0 
                                                        AND 
                                                        chat.sub_cat_id = 2
                                                        AND
                                                        chat_message.status_id = 1
                                                        AND
                                                        chat_message_user.unread = 1
                                                        AND
                                                        chat_message.moderation = 1
                                                        GROUP BY (order_data.order_id)
                                                        ORDER BY chat_message_user.`id` DESC')->queryAll();
                                                        
            foreach($CountsChat as $row){
                $mass['title'][] = $row['title'];
                $mass['date'][] = $row['date'];
                $mass['chat_id'][] = $row['chat_id'];
                $mass['orders_id'][] = $row['order_id'];
            }
        }
       return $mass;                                                        
    }
    
    public function ChatContestPersone($chat_id) {
    
        if(!empty($chat_id))
        {
            $chat_id = (int) $chat_id;
            $CountsChat = Yii::$app->db->createCommand('SELECT 
                                                        chat_message.chat_id as chat_id, 
                                                        chat_message.user_id as user_id, 
                                                        chat_message.message as message, 
                                                        profile.firstname as firstname,
                                                        profile.lastname as lastname
                                                        FROM chat_message 
                                                        LEFT JOIN chat_message_read ON chat_message_read.chat_message_id = chat_message.id 
                                                        LEFT JOIN profile ON profile.user_id = chat_message.user_id 
                                                        WHERE chat_message.chat_id='.$chat_id.' 
                                                        AND
                                                        chat_message.status_id = 1
                                                        AND
                                                        chat_message.moderation = 1
                                                        ORDER BY chat_message.`id` DESC')->queryAll();
            foreach($CountsChat as $row){
                $mass[] = $row;
            }
        }

        return $mass;                                                              
    }
    
    public function ReadChat($chat_id) { 
       
       if(!empty($chat_id)){
            $id = (int) $chat_id;
            if(!empty($id)){
                Yii::$app->db->createCommand('UPDATE chat_message_read SET message_read = 1 WHERE chat_message_id IN (SELECT id FROM chat_message WHERE chat_message.moderation = 1 AND chat_id = '.$id.' )')->execute();
            }
       }
    }


    public function CountsCommentUser($user_id) { 
        if(!empty($user_id))
        {
            $user_id = (int) $user_id; 
            $ChatRead = Yii::$app->db->createCommand('SELECT COUNT(chat_message.id) AS countComent
                                                      FROM `chat_message` 
                                                      LEFT JOIN chat_message_read ON chat_message_read.chat_message_id = chat_message.id  
                                                      LEFT JOIN chat ON chat.id = chat_message_read.chat_id 
                                                      LEFT JOIN orders ON orders.id = chat.creator_id
                                                      WHERE 
                                                      chat.sub_cat_id = 1 
                                                      AND 
                                                      chat_message_read.message_read = 0  
                                                      AND
                                                      chat_message.status_id = 1
                                                      AND 
                                                      orders.user_id = '.$user_id)->queryAll();
        }
        return !empty($ChatRead[0]['countComent']) ? $ChatRead[0]['countComent'] : 0;
                                                                      
    }
    
    public function NewCommentUser($creator_id, $user_id) { 
        
        if(empty($type)){
            $type = 1;
        }
        $creator_id = (int) $creator_id;
        if(!empty($user_id) && !empty($creator_id))
        {
            $ChatRead = Yii::$app->db->createCommand('SELECT chat_message.chat_id as chat_id
                                                      FROM `chat_message` 
                                                      LEFT JOIN chat_message_read ON chat_message_read.chat_message_id = chat_message.id  
                                                      LEFT JOIN chat ON chat.id = chat_message_read.chat_id 
                                                      LEFT JOIN orders ON orders.id = chat.creator_id
                                                      WHERE 
                                                      chat_message.status_id = 1
                                                      AND 
                                                      chat.sub_cat_id = 1 
                                                      AND 
                                                      chat_message_read.message_read = 0  
                                                      AND 
                                                      orders.user_id = '.$user_id.'
                                                      AND
                                                      orders.id = '.$creator_id)->queryAll();
        }
        return $ChatRead[0]['chat_id'];
                                                                      
    }
    
    public function ReadCommentUser($creator_id, $user_id) {
       if(!empty($user_id) && !empty($creator_id)){
            $id = ChatRead::NewCommentUser($creator_id, $user_id);
            if(!empty($id)){
                Yii::$app->db->createCommand('UPDATE chat_message_read SET message_read = 1 WHERE chat_id = '.$id)->execute();
            }
       }
    }
    

}