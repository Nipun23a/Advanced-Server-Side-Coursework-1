import express from "express";
import ApiKeyController from "../controllers/apiKeyController.js";
const router = express.Router();


router.post("/", ApiKeyController.createKey);
router.get("/", ApiKeyController.getKeys);
router.delete("/:id", ApiKeyController.revokeKey);
router.get("/:id/stats", ApiKeyController.getStats);


export default router;