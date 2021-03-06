<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;


/**
 * "{{%order}}".
 *
 * @property integer $id
 * @property integer $admin_id
 * @property integer $stage
 * @property integer $stage_end
 * @property integer $restaurant_id
 * @property integer $restaurant2_id
 * @property integer $created_at
 * @property integer $updated_at
 * 
 * @property User $admin
 * @property Restaurant $restaurant
 * @property Restaurant $restaurant2
 * @property OrderChoice[] $choices
 * @property array $votesList
 */
class Order extends ActiveRecord
{
    const STAGE_VOTE = 0;
    const STAGE_MEAL = 1;
    const STAGE_CLOSE = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }
    
    /**
     * Restaurant relation
     * @return ActiveQuery
     */
    public function getAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }
    
    /**
     * Choices relation
     * @return ActiveQuery
     */
    public function getChoices()
    {
        return $this->hasMany(OrderChoice::className(), ['order_id' => 'id']);
    }
    
    /**
     * Restaurant relation
     * @return ActiveQuery
     */
    public function getRestaurant()
    {
        return $this->hasOne(Restaurant::className(), ['id' => 'restaurant_id']);
    }
    
    /**
     * Restaurant relation
     * @return ActiveQuery
     */
    public function getRestaurant2()
    {
        return $this->hasOne(Restaurant::className(), ['id' => 'restaurant2_id']);
    }
    
    /**
     * Returns list of votes.
     * @return array
     */
    public function getVotesList()
    {
        $votes = [];
        
        /* @var $choice OrderChoice */
        foreach ($this->choices as $choice) {
            if (!isset($votes[$choice->restaurant_id])) {
                $votes[$choice->restaurant_id] = [
                    'votes' => 1,
                    'name' => $choice->restaurant->name,
                    'url' => $choice->restaurant->url,
                ];
            } else {
                $votes[$choice->restaurant_id]['votes'] += 1;
            }
        }
        
        if ($this->stage_end < time()) {
            uasort($votes, function ($a, $b) {
                if ($a['votes'] == $b['votes']) {
                    return 0;
                }
                return ($a['votes'] < $b['votes']) ? 1 : -1;
            });
        }
        
        return $votes;
    }
    
    /**
     * Checks if there is order opened.
     * @return bool
     */
    public static function isOpen()
    {
        return (new Query())
                ->from(static::tableName())
                ->where(['!=', 'stage', self::STAGE_CLOSE])
                ->exists();
    }
}
