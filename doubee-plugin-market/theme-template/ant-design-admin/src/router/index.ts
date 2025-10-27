import { createBrowserRouter } from "react-router-dom";
import { routes as staticRoutes } from "./routes";

const router = createBrowserRouter([...staticRoutes]);

export default router;
