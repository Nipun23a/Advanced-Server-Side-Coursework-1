import BidModel from "../models/BidModel.js";

class BiddingService {

    static async placeBid(userId, data) {
        const { bid_amount, bid_date, sponsorship_total } = data;

        const highest = await BidModel.getHighestBidForDate(bid_date);

        if (bid_amount <= highest) {
            throw new Error("Bid must be higher than current highest bid");
        }

        return BidModel.create({
            user_id: userId,
            bid_amount,
            bid_date,
            sponsorship_total
        });
    }

    static async updateBid(userId, bidId, newAmount) {
        const bid = await BidModel.findByIdAndUser(bidId, userId);
        if (!bid) throw new Error("Bid not found");

        const highest = await BidModel.getHighestBidForDate(bid.bid_date);

        if (newAmount <= highest) {
            throw new Error("New amount must be higher than current highest bid");
        }

        return BidModel.updateAmount(bidId, newAmount);
    }

    static async cancelBid(userId, bidId) {
        const bid = await BidModel.findByIdAndUser(bidId, userId);
        if (!bid) throw new Error("Bid not found");

        return BidModel.cancel(bidId);
    }

    static async getUserHistory(userId, page, limit) {
        return BidModel.getHistoryByUser(userId, page, limit);
    }

    static async selectWinner(bidDate) {
        const candidates = await BidModel.findCandidatesForDate(bidDate);

        if (candidates.length === 0) {
            throw new Error("No bids found");
        }

        const winner = candidates[0];

        await BidModel.markAsWon(winner.id);
        await BidModel.markOthersAsLost(bidDate, winner.id);

        return winner;
    }
}

export default BiddingService;