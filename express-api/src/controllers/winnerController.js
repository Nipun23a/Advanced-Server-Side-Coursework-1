import WinnerService from "../services/WinnerService.js";
import {sendError, sendSuccess} from "../utils/responseHelper.js";
import {logger} from "../config/logger.js";

class WinnerController {
    static async getTodayFeatures(req,res) {
        try{
            const result = await WinnerService.getTodayFeatureProfile();
            if (!result){
                return sendError(
                    res,
                    'NO_FEATURED_ALUMNI',
                    'No alumni is featured today.Please check back tomorrow.',
                    404
                );
            }
        }catch (error){
            logger.error('WinnerController.getTodayFeatures FAILED:', {
                message: error.message,
                stack: error.stack,
                ip:req.ip,
                apiKeyId:req.apiKey? req.apiKey.id : null,
            });
            return sendError(
                res,
                'FEATURED_RETRIEVAL_ERROR',
                'Failed to retrieve featured alumni. Please try again later.',
                500
            );
        }
    }

    static async getFeaturedHistory(req,res) {
        try{
            const page = parseInt(req.query.page) || 1;
            const limit = Math.min(parseInt(req.query.limit) || 10, 100);
            const result = await WinnerService.getFeaturedHistory(page, limit);
            return sendSuccess(res,result,'Featured alumni history retrieved successfully.');
        }catch (error){
            logger.error('WinnerController.getFeaturedHistory FAILED:', {
                message: error.message,
                stack: error.stack,
            });
            return sendError(
                res,
                'HISTORY_RETRIEVAL_ERROR',
                'Failed to retrieve featured alumni history. Please try again later.',
                500
            );
        }
    }
    static async getTodayWinner(req,res) {
        try{
            const result = await WinnerService.getTodayWinner();
            if (!result){
                return sendError(
                    res,
                    'NO_WINNER',
                    'No winner is selected today',
                    404
                );
            }
            return sendSuccess(res,result,'Today\'s winner retrieved successfully.');
        }catch (error){
            logger.error('WinnerController.getTodayWinner error:', {
                message: error.message,
                stack: error.stack,
            });
            return sendError(
                res,
                'WINNER_RETRIEVAL_ERROR',
                'Failed to retrieve today\'s winner. Please try again later.',
                 500
            );
        }
    }
    static async getWinnerHistory(req,res) {
        try{
            const page = parseInt(req.query.page) || 1;
            const limit = Math.min(parseInt(req.query.limit) || 10, 100);
            const result = await WinnerService.getWinnerHistory(page, limit);
            return sendSuccess(res,result,'Winner history retrieved successfully.');
        }catch (error){
            logger.error('WinnerController.getWinnerHistory FAILED:', {
                message: error.message,
                stack: error.stack,
            });
            return sendError(
                res,
                'HISTORY_RETRIEVAL_ERROR',
                'Failed to retrieve winner history. Please try again later.',
                 500
            );
        }
    }
    static async triggerManualSelection(req,res){
        try {
            logger.info('Manual winner selection triggered',{
                ip:req.ip,
                authType:req.authType,
            });
            const {result,message} = await WinnerService.triggerManualSelection();
            if (!result){
                return sendSuccess(res,{result:null},message);
            }
            return sendSuccess(res,result,message,201);
        }catch (error){
            logger.error('WinnerController.triggerManualSelection FAILED:', {
                message: error.message,
                stack: error.stack,
            });
            return sendError(
                res,
                'MANUAL_SELECTION_ERROR',
                'Failed to trigger manual winner selection. Please try again later.',
                  500
            );
        }
    }
    static async checkWinnerExists(req,res){
        try{
            const date = req.query.date;
            if (!date){
                return sendError(
                    res,
                    'MISSING_DATE',
                    'Date query parameter is required (format:YYYY-MM-DD)',
                    400
                );
            }

            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(date)) {
                return sendError(
                    res,
                    'INVALID_DATE_FORMAT',
                    'Date must be in YYYY-MM-DD format.',
                    400
                );
            }
            const exists = await WinnerService.hasWinnerForDate(date);
            return sendSuccess(res,{
                date,
                winner_exists:exists,
                },exists ? `A winner exists for ${date}` : `No winner selected for ${date}`);
        }catch (error){
            logger.error('WinnerController.checkWinnerExists FAILED:', {
                message: error.message,
                stack: error.stack,
            });
            return sendError(
                res,
                'CHECK_ERROR',
                'Failed to check winner status. Please try again later.',
                   500
            );
        }
    }
}
export default WinnerController;