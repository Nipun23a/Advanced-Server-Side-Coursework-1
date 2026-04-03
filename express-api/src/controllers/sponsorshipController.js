import SponsorshipService from "../services/SponsorshipService.js";

class SponsorshipController {
    static async getMyOffers(req, res, next){
        try {
            const userId = req.user.id;
            const offers = await SponsorshipService.getAllOffers(userId);
            res.json({
                success: true,
                data: offers,
            });
        }catch (error){
            next(error);
        }
    }
    static async getOffer(req,res,next){
        try {
            const {id} = req.params;
            const offer = await SponsorshipService.getOfferById(id);
            res.json({
                success: true,
                data: offer,
            });
        }catch (error){
            next(error);
        }
    }
    static async acceptOffer(req,res,next) {
        try {
            const {id} = req.params;
            await SponsorshipService.acceptOffer(id);
            res.json({
                success: true,
                message: 'Offer accepted successfully',
            });
        }catch (error){
            next(error);
        }
    }

    static async declineOffer(req,res,next) {
        try {
            const {id}  = req.params;
            await SponsorshipService.declineOffer(id);
            res.json({
                success: true,
                message: 'Offer declined successfully',
            });
        }catch (error){
            next(error);
        }
    }

    static async getBalance(req,res,next) {
        try{
            const userId = req.user.id;
            const balance = await SponsorshipService.getAvailableFunds(userId);
            res.json({
                success: true,
                data: balance,
            });
        }catch (error){
            next(error);
        }
    }
}
export default SponsorshipController;