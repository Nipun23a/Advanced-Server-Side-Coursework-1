import nodemailer from "nodemailer";
import {logger} from "../config/logger.js";

class NotificationService {
    static transporter = null;
    static disabledReasonLogged = false;

    static getMailConfig() {
        return {
            host: process.env.SMTP_HOST,
            port: parseInt(process.env.SMTP_PORT, 10) || 587,
            user: process.env.SMTP_USER,
            pass: process.env.SMTP_PASS,
            from: process.env.NOTIFICATION_FROM_EMAIL || process.env.SMTP_USER || 'noreply@eastminster.ac.uk',
        };
    }

    static isConfigured() {
        const { host, user, pass } = this.getMailConfig();
        return Boolean(host && user && pass && !host.startsWith('YOUR_') && !user.startsWith('YOUR_') && !pass.startsWith('YOUR_'));
    }

    static getTransporter() {
        if (!this.isConfigured()) {
            if (!this.disabledReasonLogged) {
                logger.warn('NotificationService: SMTP not configured. Winner emails will be skipped.');
                this.disabledReasonLogged = true;
            }
            return null;
        }

        if (!this.transporter) {
            const { host, port, user, pass } = this.getMailConfig();
            this.transporter = nodemailer.createTransport({
                host,
                port,
                secure: port === 465,
                auth: {
                    user,
                    pass,
                },
            });
        }

        return this.transporter;
    }

    static async sendMail({ to, subject, text, html }) {
        const transporter = this.getTransporter();
        if (!transporter) {
            return false;
        }

        const { from } = this.getMailConfig();
        await transporter.sendMail({ from, to, subject, text, html });
        return true;
    }

    static buildWinnerMessage({ featuredDate, bidAmount }) {
        const subject = `You won Alumni of the Day for ${featuredDate}`;
        const text = [
            `Congratulations.`,
            ``,
            `You won the Alumni of the Day bid for ${featuredDate}.`,
            `Winning bid amount: £${Number(bidAmount).toFixed(2)}`,
            ``,
            `Your profile will be featured for the full day.`,
        ].join('\n');
        const html = `
            <p>Congratulations.</p>
            <p>You won the <strong>Alumni of the Day</strong> bid for <strong>${featuredDate}</strong>.</p>
            <p>Winning bid amount: <strong>£${Number(bidAmount).toFixed(2)}</strong></p>
            <p>Your profile will be featured for the full day.</p>
        `;

        return { subject, text, html };
    }

    static buildLoserMessage({ featuredDate, bidAmount }) {
        const subject = `Bid result for ${featuredDate}`;
        const text = [
            `Your bid for ${featuredDate} was not selected.`,
            ``,
            `Your bid amount: £${Number(bidAmount).toFixed(2)}`,
            `You can place a new bid for the next eligible auction before the cutoff.`,
        ].join('\n');
        const html = `
            <p>Your bid for <strong>${featuredDate}</strong> was not selected.</p>
            <p>Your bid amount: <strong>£${Number(bidAmount).toFixed(2)}</strong></p>
            <p>You can place a new bid for the next eligible auction before the cutoff.</p>
        `;

        return { subject, text, html };
    }

    static async sendWinnerSelectionNotifications({ winner, losers, featuredDate }) {
        const winnerMail = this.buildWinnerMessage({
            featuredDate,
            bidAmount: winner.bid_amount,
        });

        const jobs = [
            this.sendMail({
                to: winner.email,
                ...winnerMail,
            }).then((sent) => ({ type: 'winner', email: winner.email, success: sent }))
              .catch((error) => ({ type: 'winner', email: winner.email, success: false, error })),
            ...losers.map((loser) => {
                const loserMail = this.buildLoserMessage({
                    featuredDate,
                    bidAmount: loser.bid_amount,
                });

                return this.sendMail({
                    to: loser.email,
                    ...loserMail,
                }).then((sent) => ({ type: 'loser', email: loser.email, success: sent }))
                  .catch((error) => ({ type: 'loser', email: loser.email, success: false, error }));
            }),
        ];

        const results = await Promise.all(jobs);
        const sent = results.filter((result) => result.success).length;
        const failed = results.filter((result) => !result.success);

        failed.forEach((result) => {
            if (!result.error) {
                return;
            }
            logger.error('NotificationService: email send failed', {
                recipientType: result.type,
                email: result.email,
                message: result.error.message,
            });
        });

        logger.info(`NotificationService: ${sent}/${results.length} winner-selection email(s) sent for ${featuredDate}`);

        return {
            attempted: results.length,
            sent,
            failed: failed.length,
        };
    }
}

export default NotificationService;
