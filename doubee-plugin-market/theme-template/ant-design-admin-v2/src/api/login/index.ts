import request from "@/utils/request";
import type { LoginParams, LoginResponse } from "./model";

export function login(params: LoginParams): Promise<LoginResponse> {
    return request.post("/auth/login", params);
}