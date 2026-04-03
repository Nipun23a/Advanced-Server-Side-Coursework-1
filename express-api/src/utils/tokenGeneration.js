import crypto from 'crypto';

class TokenGeneration{
    static generateToken(byteLength = 32) {
        return crypto.randomBytes(byteLength).toString('hex');
    }

    static generateAPIKey(prefix= null,byteLength = 32){
        const keyPrefix = prefix || process.env.API_KEY_PREFIX || 'alum_';
        const randomPart = crypto.randomBytes(byteLength).toString('hex');
        return `${keyPrefix}${randomPart}`;
    }

    static hashToken(rawToken) {
        return crypto.createHash('sha256').update(rawToken).digest('hex');
    }

    static timingSafeCompare(provided, expected) {
        if (!provided || !expected) {
            return false;
        }
        const providedBuffer = Buffer.from(provided, 'utf-8');
        const expectedBuffer = Buffer.from(expected, 'utf-8');
        if (providedBuffer.length !== expectedBuffer.length) {
            return false;
        }

        return crypto.timingSafeEqual(providedBuffer, expectedBuffer);
    }

    static generateSecret() {
        return crypto.randomBytes(64).toString('hex');
    }

    static generateVerificationCode() {
        return crypto.randomInt(100000, 999999).toString();
    }

    static isValidTokenFormat(token, minLength = 32) {
        if (!token || typeof token !== 'string') {
            return false;
        }
        if (token.length < minLength) {
            return false;
        }
        return /^[0-9a-f]+$/i.test(token);
    }

    static isValidAPIKeyFormat(apiKey) {
        if (!apiKey || typeof apiKey !== 'string') {
            return false;
        }
        const prefix = process.env.API_KEY_PREFIX || 'alum_';

        if (!apiKey.startsWith(prefix)) {
            return false;
        }
        const keyBody = apiKey.substring(prefix.length);
        return keyBody.length >= 32 && /^[0-9a-f]+$/i.test(keyBody);
    }
}

export default TokenGeneration;