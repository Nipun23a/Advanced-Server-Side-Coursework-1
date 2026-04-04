import express from "express";
const router = express.Router();
router.get("/", (req, res) => {
    res.json({
        success: true,
        data: []
    });
});
router.post("/", (req, res) => {
    res.json({
        success: true,
        data: {
            api_key: "alum_test123456"
        }
    });
});
router.delete("/:id", (req, res) => {
    res.json({
        success: true,
        message: "Key revoked"
    });
});

export default router;