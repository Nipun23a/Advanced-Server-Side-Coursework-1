import crypto from "crypto";
import ApiKeyModel from "../models/ApiKeyModel.js";
import ApiUsageLogModel from "../models/ApiUsageLogModel.js";

class ApiKeyService {

    static generateKey() {
        return "alum_" + crypto.randomBytes(16).toString("hex");
    }

    static hashKey(key) {
        return crypto.createHash("sha256").update(key).digest("hex");
    }

    static async createKey(userId) {
        const apiKey = this.generateKey();
        const hash = this.hashKey(apiKey);

        const saved = await ApiKeyModel.create(userId, hash);

        return {
            id: saved.id,
            api_key: apiKey
        };
    }

    static async getKeys(userId) {
        return ApiKeyModel.findAllByUser(userId);
    }

    static async revokeKey(keyId, userId) {
        const key = await ApiKeyModel.findByIdAndUser(keyId, userId);

        if (!key) throw new Error("Key not found");

        return ApiKeyModel.revoke(keyId);
    }

    static async getStats(apiKeyId) {
        return ApiUsageLogModel.getUsageByDate(apiKeyId);
    }
}

export default ApiKeyService;