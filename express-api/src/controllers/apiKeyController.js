import ApiKeyService from "../services/ApiKeyService.js";

class ApiKeyController{
    static async createKey(req,res,next){
        try{
            const userId = 1
            const key = await ApiKeyService.createKey(userId);
            res.json({
                success: true,
                data: key,
            });
        }catch (error){
            next(error);
        }
    }

    static async getKeys(req,res,next){
        try{
            const userId = 1;
            const keys = await ApiKeyService.getKey(userId);
            res.json({
                success: true,
                data: keys,
            });
        }catch (error){
            next(error);
        }
    }

    static async revokeKey(req,res,next){
        try{
            const {id} = req.params;
            await ApiKeyService.revokeKey(id);
            res.json({
                success: true,
                message: 'API key revoked successfully',
            });
        }catch (error){
            next(error);
        }
    }

    static async getStats(req,res,next){
        try{
            const {id} = req.params;
            const stats = await ApiKeyService.getStats(id);
            res.json({
                success: true,
                data: stats,
            });
        }catch (error){
            next(error);
        }
    }
}

export default ApiKeyController;