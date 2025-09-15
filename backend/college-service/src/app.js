import 'dotenv/config';
import express from 'express';
import helmet from 'helmet';
import cors from 'cors';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import sequelize from './db/sequelize.js';
import cookieParser from "cookie-parser";
import collegeRoutes from './routes/college.routes.js';
import branchRoutes from './routes/branch.routes.js';



import dotenv from 'dotenv';

dotenv.config()

const app = express();

app.use(cors({ 
  origin: process.env.CORS_ORIGIN || '*',
  credentials: true, // allow cookies
}));

app.use(helmet());
app.use(express.json());
app.use(morgan('dev'));
app.use(cookieParser());
app.use(rateLimit({ windowMs: 60_000, max: 200 }));

app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: process.env.SERVICE_NAME });
});

app.get("/", (req, res) => {
  res.send("Welcome to the College Service API");
});

app.use('/', collegeRoutes);
app.use('/branch', branchRoutes);


export async function initDb() {
  await sequelize.authenticate();
  await sequelize.sync();
  console.log('🗄️  Database connected and synced---'); 
}

export default app;
