import { lazy } from "react";
import { RouteObject } from "react-router";

/**
 * 静态路由
 */
export const routes: RouteObject[] = [
  {
    path: "/login",
    Component: lazy(() => import("@/pages/Auth/Login")),
  },
  {
    path: "/dashboard/console",
    Component: lazy(() => import("@/pages/Dashboard/Console")),
  },
  {
    path: "/system/user",
    Component: lazy(() => import("@/pages/System/User")),
  },
];
