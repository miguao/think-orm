import request from "@/utils/request";
import type { MeInfo } from "./model";

export function getMeInfo(): Promise<MeInfo> {
    return request.get("/personal/account/getMeInfo");
}