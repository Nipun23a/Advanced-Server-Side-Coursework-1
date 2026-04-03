import SponsorshipOfferModel from "../models/SponsorshipOfferModel.js";
import sponsorshipOfferModel from "../models/SponsorshipOfferModel.js";

class SponsorshipService{
    static async getAvailableFunds(alumniId){
        return await SponsorshipOfferModel.getAvailableBalance(alumniId);
    }

    static async acceptOffer(offerId){
        const offer = await SponsorshipOfferModel.findById(offerId);
        if(!offer) throw new Error('Offer not found');
        if(offer.status !== 'pending') throw new Error('Only pending offers can be accepted');

        return await SponsorshipOfferModel.updateStatus(offerId, 'accepted');
    }

    static async declineOffer(offerId){
        const offer = await sponsorshipOfferModel.findAllByAlumni(offerId);
        if (!offer) throw new Error('Offer not found');
        if (offer.status !== 'pending') throw new Error('Only pending offers can be declined');
        return await SponsorshipOfferModel.updateStatus(offerId, 'declined');
    }

    static async getAllOffers(alumniId){
        return await SponsorshipOfferModel.findById(alumniId);
    }

    static async getBalanceDetails(alumniId){
        return await SponsorshipOfferModel.getBalanceDetails(alumniId);
    }

    static async validateFunds(alumniId, bidAmount){
        const available = await SponsorshipOfferModel.getAvailableBalance(alumniId);
        if (bidAmount > available){
            throw new Error("Insufficient sponsorship funds to place this bid");
        }
        return true;
    }

    static async deductFundsAfterWin(alumniId){
        return await SponsorshipOfferModel.markAllAsPaidForAlumni(alumniId);
    }

    static async getOfferById(offerId){
        const offer= await SponsorshipOfferModel.findById(offerId);
        if (!offer){
            throw new Error("Offer not found");
        }
        return offer;
    }
}

export default SponsorshipService;