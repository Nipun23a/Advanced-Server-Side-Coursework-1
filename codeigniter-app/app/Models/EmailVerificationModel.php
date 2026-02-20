<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailVerificationModel extends Model
{
    protected $table = 'email_verification_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'token_hash',
        'expired_at',
        'used_at'
    ];

    protected $useTimestamps = true;

    public function createToken(int $userId) :string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256',$rawToken);
        $expiredAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this -> insert([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expired_at' => $expiredAt
        ]);
        return $rawToken;
    }

    public function validateToken(string $rawToken) : ?array
    {
        $tokenHash = hash('sha256',$rawToken);
        return $this-> where ('token_hash', $tokenHash)
            -> where ('used_at', null)
            -> where ('expired_at >', date('Y-m-d H:i:s'))
            -> first();
    }

    public function markAsUsed(int $tokenId): bool
    {
        return $this -> update($tokenId,[
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function invalidateUserToken (int $userId) : void
    {
        $this -> where ('user_id', $userId)
        -> where('used_at', null)
        ->set('used_at', date('Y-m-d H:i:s'))
        ->update();
    }
}
