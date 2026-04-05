import SponsorModel from '../models/SponsorModel.js';
import SponsorshipOfferModel from '../models/SponsorshipOfferModel.js';
import { logger } from '../config/logger.js';

class SponsorshipService {
    static async createSponsor(data) {
        const { sponsor_name, sponsor_type, website_url } = data;
        const validTypes = ['course_provider', 'licensing_body', 'certification_body'];
        if (!validTypes.includes(sponsor_type)) {
            const error = new Error(`Invalid sponsor type. Must be one of: ${validTypes.join(', ')}`);
            error.code = 'INVALID_SPONSOR_TYPE';
            error.status = 400;
            throw error;
        }

        const existing = await SponsorModel.findByName(sponsor_name);
        if (existing) {
            const error = new Error('A sponsor with this name already exists.');
            error.code = 'DUPLICATE_SPONSOR';
            error.status = 409;
            throw error;
        }

        const result = await SponsorModel.create({ sponsor_name, sponsor_type, website_url });

        logger.info(`Sponsor created: id=${result.insertId}, name=${sponsor_name}, type=${sponsor_type}`);

        return {
            id: result.insertId,
            sponsor_name,
            sponsor_type,
            website_url,
        };
    }

    static async getAllSponsors(sponsorType = null) {
        const sponsors = await SponsorModel.findAll(sponsorType);
        return { sponsors };
    }

    static async getSponsorById(sponsorId) {
        const sponsor = await SponsorModel.findById(sponsorId);

        if (!sponsor) {
            const error = new Error('Sponsor not found.');
            error.code = 'SPONSOR_NOT_FOUND';
            error.status = 404;
            throw error;
        }

        return sponsor;
    }
    static async createOffer(data) {
        const { sponsor_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount } = data;
        const normalizedSponsorableType = SponsorshipOfferModel.normalizeSponsorableType(sponsorable_type);
        const validTypes = ['certificate', 'license', 'licence', 'professional_course'];

        if (!validTypes.includes(sponsorable_type)) {
            const error = new Error(`Invalid sponsorable type. Must be one of: ${validTypes.join(', ')}`);
            error.code = 'INVALID_SPONSORABLE_TYPE';
            error.status = 400;
            throw error;
        }
        const sponsor = await SponsorModel.findById(sponsor_id);
        if (!sponsor) {
            const error = new Error('Sponsor not found.');
            error.code = 'SPONSOR_NOT_FOUND';
            error.status = 404;
            throw error;
        }
        const alumniProfile = await SponsorshipOfferModel.findAlumniProfileById(alumni_id);
        if (!alumniProfile) {
            const error = new Error('Alumni profile not found for the supplied alumni_id.');
            error.code = 'ALUMNI_PROFILE_NOT_FOUND';
            error.status = 404;
            throw error;
        }

        const credentialName = await SponsorshipOfferModel.getCredentialName(normalizedSponsorableType, sponsorable_id);
        if (!credentialName) {
            const error = new Error('The specified credential does not exist.');
            error.code = 'CREDENTIAL_NOT_FOUND';
            error.status = 404;
            throw error;
        }

        const result = await SponsorshipOfferModel.create({
            sponsorship_id: sponsor_id,
            alumni_id,
            sponsorable_id,
            sponsorable_type: normalizedSponsorableType,
            offer_amount,
        });

        logger.info(
            `Sponsorship offer created: id=${result.insertId}, sponsor=${sponsor.sponsor_name}, ` +
            `alumni_id=${alumni_id}, credential=${credentialName}, amount=${offer_amount}`
        );

        return {
            id: result.insertId,
            sponsor_id,
            sponsor_name: sponsor.sponsor_name,
            alumni_id,
            sponsorable_type: normalizedSponsorableType,
            sponsorable_id,
            credential_name: credentialName,
            offer_amount,
            status: 'pending',
        };
    }
    static async getOffers(alumniId) {
        const offers = await SponsorshipOfferModel.findAllByAlumni(alumniId);

        const enrichedOffers = await Promise.all(
            offers.map(async (offer) => {
                const credentialName = await SponsorshipOfferModel.getCredentialName(
                    offer.sponsorable_type,
                    offer.sponsorable_id
                );

                return {
                    ...offer,
                    credential_name: credentialName || 'Unknown credential',
                };
            })
        );

        return { offers: enrichedOffers };
    }
    static async getOffersByStatus(alumniId, status) {
        const offers = await SponsorshipOfferModel.findByAlumniAndStatus(alumniId, status);
        return { offers };
    }
    static async respondToOffer(offerId, alumniId, action) {

        const offer = await SponsorshipOfferModel.findByIdAndAlumni(offerId, alumniId);

        if (!offer) {
            const error = new Error('Sponsorship offer not found.');
            error.code = 'OFFER_NOT_FOUND';
            error.status = 404;
            throw error;
        }
        if (offer.status !== 'pending') {
            const error = new Error(`This offer has already been ${offer.status}. Cannot change response.`);
            error.code = 'OFFER_ALREADY_RESPONDED';
            error.status = 400;
            throw error;
        }
        const newStatus = action === 'accept' ? 'accepted' : 'declined';

        await SponsorshipOfferModel.updateStatus(offerId, newStatus);

        logger.info(`Sponsorship offer ${offerId} ${newStatus} by user ${alumniId}`);

        return {
            id: offerId,
            status: newStatus,
            offer_amount: parseFloat(offer.offer_amount),
            sponsorable_type: offer.sponsorable_type,
        };
    }
    static async getBalance(alumniId) {
        const details = await SponsorshipOfferModel.getBalanceDetails(alumniId);
        return details;
    }
    static async getOfferSummary(alumniId) {
        const counts = await SponsorshipOfferModel.countByAlumniAndStatus(alumniId);

        const summary = {
            pending: 0,
            accepted: 0,
            declined: 0,
            paid: 0,
        };

        counts.forEach((row) => {
            summary[row.status] = row.count;
        });

        return { summary };
    }
}

export default SponsorshipService;
