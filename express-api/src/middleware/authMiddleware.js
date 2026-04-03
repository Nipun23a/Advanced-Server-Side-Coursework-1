import crypto from "crypto";
import {pool} from "../config/database.js";


const hashToken = (token) =>
  crypto.createHash("sha256").update(token).digest("hex");

export const authMiddleware = async (req, res, next) => {
  try {
    const header = req.headers.authorization;

    if (!header || !header.startsWith("Bearer ")) {
      return res.status(401).json({ error: "Unauthorized" });
    }

    const token = header.split(" ")[1];
    const hashed = hashToken(token);

    const [rows] = await pool.execute(
      "SELECT * FROM api_keys WHERE key_hash = ? AND is_active = 1",
      [hashed]
    );

    if (rows.length === 0) {
      return res.status(401).json({ error: "Invalid token" });
    }

    req.apiKey = rows[0];
    next();
  } catch (err) {
    next(err);
  }
};
