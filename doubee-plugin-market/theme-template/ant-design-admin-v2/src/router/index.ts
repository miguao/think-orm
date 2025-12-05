import { createBrowserRouter } from "react-router-dom";
import { createElement } from "react";

import BasicLayout from "@/layouts/BasicLayout";
import DashboardConsolePage from "@/pages/dashboard/console";
import LoginPage from "@/pages/login";
import SystemUserPage from "@/pages/system/user";

export const staticRoutes = [
    {
        path: "/login",
        element: createElement(LoginPage),
    },
    {
        path: "/",
        element: createElement(BasicLayout),
        children: [
            {
                index: true,
                element: createElement(DashboardConsolePage),
            },
            {
                path: "/system/user",
                element: createElement(SystemUserPage),
            },
        ],
    },
];

export const router = createBrowserRouter(staticRoutes);
