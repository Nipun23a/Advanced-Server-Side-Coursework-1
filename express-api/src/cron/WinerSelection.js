import cron from 'node-cron';
import WinnerService from "../services/WinnerService.js";
import {logger} from "../config/logger.js";

const startCronJobs = () => {
    cron.schedule('0 18 * * *',async () => {
        logger.info('=== CRON: Daily winner selection starting ===');
        try {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate()+1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            const alreadySelected = await WinnerService.hasWinnerForDate(tomorrowStr);
            if (alreadySelected){
                logger.warn(`CRON: Winner already selected for ${tomorrowStr}. Skipping.`);
                return;
            }
            const result = await WinnerService.selectDailyWinner();
            if (result){
                logger.info(
                    `CRON: Winner selected successfully — ` +
                    `user_id=${result.winner_user_id}, ` +
                    `bid_amount=${result.bid_amount}, ` +
                    `featured_date=${result.featured_date}, ` +
                    `losers=${result.losers_count}, ` +
                    `sponsorships_paid=${result.sponsorships_paid}`
                );

                if (result.notifications) {
                    logger.info(
                        `CRON: Notifications â€” attempted=${result.notifications.attempted}, ` +
                        `sent=${result.notifications.sent}, failed=${result.notifications.failed}`
                    );
                }

            }else{
                logger.info('CRON: No winner selected — no eligible bids found.');
            }
        }catch (error){
            logger.error('CRON: Winner selection FAILED:', {
                message: error.message,
                stack: error.stack,
            });
        }
        logger.info('=== CRON: Daily winner selection finished ===');
    },{
        timezone:process.env.BID_TIMEZONE || 'Europe/London',
        scheduled:true,
    });

    logger.info('CRON: Daily winner selection scheduled — 6:00 PM Europe/London');

    cron.schedule('0 0 1 * *',async () => {
        logger.info('=== CRON:Monthly cleanup starting ===');
        try {
            logger.info('CRON: Monthly cleanup completed — no action needed (records auto-create per month)');

        }catch (error){
            logger.error('CRON: Monthly cleanup FAILED:', {
                message: error.message,
                stack: error.stack,
            });

            logger.info('=== CRON: Monthly cleanup finished ===');
        }
    },{
        timezone:process.env.BID_TIMEZONE || 'Europe/London',
        scheduled:true,
    });
    logger.info('CRON: Monthly cleanup scheduled — midnight 1st of each month Europe/London');
}

export default startCronJobs;
