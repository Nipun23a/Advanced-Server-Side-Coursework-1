export const internalAuthMiddleware = (req, res, next) => {
    const internalHeader = req.headers['x-internal-key'];

    if (!internalHeader || internalHeader !== process.env.INTERNAL_API_TOKEN) {
        return res.status(403).json({ error: "Forbidden - Internal only" });
    }
    next();
}