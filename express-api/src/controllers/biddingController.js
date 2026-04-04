import BiddingService from "../services/BiddingService.js";

class BiddingController {
    static async placeBid(req,res,next){
        try{
            const userId = req.user.id || 1;
            const bid = await BiddingService.placeBid(userId,req.body);
            res.json({
                success: true,
                data: bid,
            });
        }catch (error){
            next(error);
        }
    }

    static async updateBid(req,res,next){
        try {
            const userId = req.user.id || 1;
            const {id} = req.params;
            const {amount} = req.body;
            await BiddingService.updateBid(userId,id,amount);
            res.json({
                success: true,
                message: 'Bid updated successfully',
            });
        }catch (error){
            next(error);
        }
    }

    static async cancelBid(req,res,next){
        try {
            const userId = req.user.id || 1;
            const {id} = req.params;
            await BiddingService.cancelBid(userId,id);
            res.json({
                success: true,
                message: 'Bid cancelled successfully',
            });
        }catch (error){
            next(error);
        }
    }

    static async history(req,res,next){
        try {
            const userId = req.user.id || 1;
            const {page,limit} = req.query;
            const history = await BiddingService.getUserHistory(userId,page,limit);
            res.json({
                success: true,
                data: history,
            });
        }catch (error){
            next(error);
        }
    }
    static async selectWinner(req,res,next){
        try {
            const {date} = req.params;
            const winner = await BiddingService.selectWinner(date);
            res.json({
                success: true,
                data: winner,
            })
        }catch (error){
            next(error);
        }
    }
}